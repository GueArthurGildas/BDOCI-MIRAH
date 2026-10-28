<?php

namespace Modules\Tracking\Tests\Feature;

use App\Enums\ZoneType;
use App\Models\Position;
use App\Models\Vessel;
use App\Support\Vms\GeoCalculator;
use App\Support\Vms\PositionPartitions;
use App\Support\Vms\TrackBuilder;
use App\Support\Vms\ZoneLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Attributes\Covers;
use Tests\Concerns\CreatesZones;
use Tests\TestCase;

class PositionStorageTest extends TestCase
{
    use CreatesZones, RefreshDatabase;

    private function partitionOf(int $id): string
    {
        return DB::selectOne('SELECT tableoid::regclass::text AS name FROM positions WHERE id = ?', [$id])->name;
    }

    #[Covers('UC05', partial: true)]
    public function test_positions_are_stored_in_monthly_partitions(): void
    {
        $this->assertSame(['p'], [DB::selectOne("SELECT relkind FROM pg_class WHERE relname = 'positions'")->relkind]);

        $position = Position::factory()->create(['recorded_at' => '2026-11-15 10:00:00']);
        $other = Position::factory()->create(['recorded_at' => '2026-12-02 10:00:00']);

        $this->assertSame('positions_2026_11', $this->partitionOf($position->id));
        $this->assertSame('positions_2026_12', $this->partitionOf($other->id));
    }

    public function test_positions_outside_the_prepared_months_land_in_the_default_partition_until_the_month_is_created(): void
    {
        $position = Position::factory()->create(['recorded_at' => '2031-03-10 08:00:00']);
        $this->assertSame('positions_default', $this->partitionOf($position->id));

        $this->assertTrue(app(PositionPartitions::class)->ensure(now()->setDate(2031, 3, 1)));
        $this->assertFalse(app(PositionPartitions::class)->ensure(now()->setDate(2031, 3, 1)));

        $this->assertSame('positions_2031_03', $this->partitionOf($position->id));
        $this->assertSame(1, Position::count());
    }

    public function test_the_partition_command_prepares_the_coming_months(): void
    {
        $this->artisan('tracking:create-position-partitions', ['--months' => 40])->assertSuccessful();

        $this->assertNotNull(DB::selectOne('SELECT to_regclass(?) AS name', ['positions_'.now()->addMonths(40)->format('Y_m')])->name);
    }

    public function test_the_new_position_columns_are_stored_and_the_index_plan_exists(): void
    {
        $position = Position::factory()->create(['received_at' => now(), 'activity' => 'fishing', 'navigation_status' => 'engaged', 'accuracy_m' => 25, 'retention_reason' => 'infraction']);

        $this->assertSame([25, 'fishing'], [$position->fresh()->accuracy_m, $position->fresh()->activity]);

        $indexes = collect(DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'positions'"))->pluck('indexname');
        foreach (['positions_vessel_id_recorded_at_index', 'positions_zone_id_recorded_at_index', 'positions_recorded_at_brin', 'positions_point_gist'] as $index) {
            $this->assertTrue($indexes->contains($index), $index);
        }
    }

    public function test_the_zone_geometry_is_mirrored_into_a_postgis_column(): void
    {
        $zone = $this->squareZone('Test', ZoneType::PortArea, 5.0, -4.0, 0.1);

        $this->assertSame('POLYGON', DB::selectOne('SELECT GeometryType(geom) AS type FROM zones WHERE id = ?', [$zone->id])->type);
        $this->assertSame(4326, (int) DB::selectOne('SELECT ST_SRID(geom) AS srid FROM zones WHERE id = ?', [$zone->id])->srid);
        $this->assertSame($zone->id, app(ZoneLocator::class)->zoneIdFor(5.02, -4.01));
        $this->assertNull(app(ZoneLocator::class)->zoneIdFor(7.0, -4.0));
    }

    #[Covers('RG06', partial: true)]
    public function test_the_distance_to_the_coast_is_computed_in_nautical_miles(): void
    {
        $coast = ['type' => 'LineString', 'coordinates' => [[-5.0, 5.0], [-4.0, 5.0]]];

        $distance = app(GeoCalculator::class)->distanceToCoastNauticalMiles(4.5, -4.5, $coast);

        $this->assertEqualsWithDelta(30.0, $distance, 0.3);
    }

    #[Covers('RG06', partial: true)]
    public function test_zones_traversed_and_nautical_miles_per_zone_come_from_the_track(): void
    {
        $west = $this->squareZone('West', ZoneType::TerritorialSea, 5.0, -5.0, 0.5);
        $east = $this->squareZone('East', ZoneType::Restricted, 5.0, -3.0, 0.5);
        $vessel = Vessel::factory()->create();
        foreach ([[-5.0, '08:00'], [-4.0, '10:00'], [-3.0, '12:00']] as [$longitude, $time]) {
            Position::factory()->for($vessel)->create(['latitude' => 5.0, 'longitude' => $longitude, 'recorded_at' => today()->format('Y-m-d').' '.$time]);
        }
        $geo = app(GeoCalculator::class);

        $traversed = $geo->zonesTraversed($vessel->id, today(), today()->addDay());
        $miles = $geo->nauticalMilesPerZone($vessel->id, today(), today()->addDay());

        $this->assertSame([$west->id, $east->id], $traversed->all());
        $this->assertEqualsWithDelta(30.0, $miles[$west->id], 0.5);
        $this->assertEqualsWithDelta(30.0, $miles[$east->id], 0.5);
        $this->assertEqualsWithDelta(120.0, $geo->distanceNauticalMiles($vessel->id, today(), today()->addDay()), 1.0);
    }

    public function test_a_track_is_built_as_a_line_from_the_positions(): void
    {
        $vessel = Vessel::factory()->create();
        Position::factory()->for($vessel)->create(['latitude' => 5.0, 'longitude' => -5.0, 'recorded_at' => now()->subHours(3)]);
        Position::factory()->for($vessel)->create(['latitude' => 5.0, 'longitude' => -4.0, 'recorded_at' => now()->subHours(1)]);

        $track = app(TrackBuilder::class)->build($vessel->id, now()->subDay(), now());

        $this->assertSame('LINESTRING', DB::selectOne('SELECT GeometryType(geometry) AS type FROM tracks WHERE id = ?', [$track->id])->type);
        $this->assertNull(app(TrackBuilder::class)->build(Vessel::factory()->create()->id, now()->subDay(), now()));
    }
}
