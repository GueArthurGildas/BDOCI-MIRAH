<?php

namespace Modules\Tracking\Tests\Feature;

use App\Enums\ZoneType;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\EmissionCutoff;
use App\Models\MonthlyAggregate;
use App\Models\Position;
use App\Models\PositionDailySummary;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Models\VmsCutoffAuthorization;
use App\Support\Vms\EmissionRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\Concerns\CreatesZones;
use Tests\TestCase;

class EmissionAggregationTest extends TestCase
{
    use CreatesZones, RefreshDatabase;

    private function emitEveryHour(Vessel $vessel, Carbon $from, int $hours, array $attributes = []): void
    {
        foreach (range(0, $hours - 1) as $offset) {
            Position::factory()->for($vessel)->create([...$attributes, 'recorded_at' => $from->copy()->addHours($offset)->addMinutes(10)]);
        }
    }

    #[Covers('RG04')]
    #[Assumes('H-3b-3')]
    public function test_an_ivorian_vessel_must_emit_over_the_whole_period(): void
    {
        $vessel = Vessel::factory()->create(['flag' => 'CI']);
        $from = Carbon::parse('2026-09-01 00:00:00');
        $this->emitEveryHour($vessel, $from, 18);

        $result = app(EmissionRate::class)->forVessel($vessel, $from, $from->copy()->addDay());

        $this->assertEquals([24, 18, 75.0], [$result['required_hours'], $result['emitted_hours'], $result['rate']]);
    }

    #[Covers('RG04')]
    #[Assumes('H-3b-3')]
    public function test_an_authorized_technical_stop_is_removed_from_the_required_period(): void
    {
        $vessel = Vessel::factory()->create(['flag' => 'CI']);
        $from = Carbon::parse('2026-09-01 00:00:00');
        $this->emitEveryHour($vessel, $from, 18);
        VmsCutoffAuthorization::factory()->create(['vessel_id' => $vessel->id, 'started_at' => $from->copy()->addHours(18), 'ended_at' => $from->copy()->addHours(24)]);

        $result = app(EmissionRate::class)->forVessel($vessel, $from, $from->copy()->addDay());

        $this->assertEquals([18, 100.0], [$result['required_hours'], $result['rate']]);

        EmissionCutoff::factory()->create(['vessel_id' => $vessel->id, 'started_at' => $from->copy()->addHours(12), 'ended_at' => $from->copy()->addHours(15), 'is_technical_stop' => true]);
        $this->assertEquals(15, app(EmissionRate::class)->forVessel($vessel, $from, $from->copy()->addDay())['required_hours']);
    }

    #[Covers('RG04')]
    #[Assumes('H-3b-3')]
    public function test_a_foreign_vessel_must_emit_only_while_inside_the_eez(): void
    {
        $this->squareZone('ZEE', ZoneType::ExclusiveEconomicZone, 4.0, -5.0, 2.0);
        $vessel = Vessel::factory()->create(['flag' => 'ES']);
        $from = Carbon::parse('2026-09-01 00:00:00');
        $this->emitEveryHour($vessel, $from->copy()->addHours(2), 3, ['latitude' => 4.0, 'longitude' => -5.0]);
        Position::factory()->for($vessel)->create(['latitude' => 4.0, 'longitude' => -5.0, 'recorded_at' => $from->copy()->addHours(7)->addMinutes(10)]);
        Position::factory()->for($vessel)->create(['latitude' => 20.0, 'longitude' => -30.0, 'recorded_at' => $from->copy()->addHours(20)]);

        $result = app(EmissionRate::class)->forVessel($vessel, $from, $from->copy()->addDay());

        $this->assertEquals([6, 4], [$result['required_hours'], $result['emitted_hours']]);
        $this->assertEquals(66.67, $result['rate']);
    }

    #[Covers('RG04')]
    public function test_a_foreign_vessel_never_seen_inside_the_eez_has_no_rate(): void
    {
        $vessel = Vessel::factory()->create(['flag' => 'ES']);
        Position::factory()->for($vessel)->create(['latitude' => 20.0, 'longitude' => -30.0, 'recorded_at' => Carbon::parse('2026-09-01 05:00:00')]);

        $result = app(EmissionRate::class)->forVessel($vessel, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-02'));

        $this->assertNull($result['rate']);
    }

    #[Covers('UC05', partial: true)]
    public function test_the_daily_summary_counts_positions_hours_and_distance(): void
    {
        $zone = $this->squareZone('Mer', ZoneType::TerritorialSea, 5.0, -4.5, 1.0);
        $vessel = Vessel::factory()->create();
        foreach ([[-5.0, '08:10'], [-4.0, '09:10']] as [$longitude, $time]) {
            Position::factory()->for($vessel)->create(['latitude' => 5.0, 'longitude' => $longitude, 'speed_knots' => 6, 'recorded_at' => '2026-09-10 '.$time]);
        }

        $this->artisan('tracking:summarize-positions', ['--date' => '2026-09-10'])->assertSuccessful();
        $this->artisan('tracking:summarize-positions', ['--date' => '2026-09-10'])->assertSuccessful();

        $summary = PositionDailySummary::where('vessel_id', $vessel->id)->first();
        $this->assertSame(1, PositionDailySummary::count());
        $this->assertEquals([2, 2.0, 6.0], [$summary->positions_count, $summary->emission_hours, $summary->average_speed]);
        $this->assertEqualsWithDelta(60.0, $summary->distance_nautical_miles, 0.5);
        $this->assertEqualsWithDelta(60.0, $summary->zones[(string) $zone->id], 0.5);
    }

    #[Covers('RG04')]
    public function test_the_monthly_aggregate_stores_the_emission_rate_and_the_silences(): void
    {
        $device = TrackingDevice::factory()->create(['vessel_id' => Vessel::factory()->create(['flag' => 'CI'])->id]);
        $this->emitEveryHour($device->vessel, Carbon::parse('2026-09-01 00:00:00'), 360);
        $rule = AlertRule::factory()->create();
        Alert::factory()->create(['vessel_id' => $device->vessel_id, 'alert_rule_id' => $rule->id, 'triggered_at' => '2026-09-12 10:00:00']);

        $this->artisan('tracking:aggregate-monthly', ['--month' => '2026-09'])->assertSuccessful();

        $aggregate = MonthlyAggregate::where('vessel_id', $device->vessel_id)->first();
        $this->assertEquals([50.0, 1], [$aggregate->emission_rate, $aggregate->silences_count]);
    }
}
