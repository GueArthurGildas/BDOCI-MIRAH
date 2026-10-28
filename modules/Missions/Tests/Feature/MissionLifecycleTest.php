<?php

namespace Modules\Missions\Tests\Feature;

use App\Enums\MissionStatus;
use App\Models\Agent;
use App\Models\Asset;
use App\Models\ExternalInspection;
use App\Models\InfractionCase;
use App\Models\Mission;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\Zone;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class MissionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function schedule(array $overrides = [])
    {
        return $this->post(route('missions.store'), array_merge([
            'nature' => 'internal',
            'type' => 'maritime',
            'objectives' => 'Patrouille en ZEE',
            'planned_start_at' => now()->addDay()->toDateTimeString(),
            'planned_end_at' => now()->addDays(3)->toDateTimeString(),
        ], $overrides));
    }

    #[Covers('UC18', partial: true)]
    #[Assumes('H-6-9')]
    public function test_scheduling_numbers_the_mission_and_records_zones_points_team_and_assets(): void
    {
        $actor = $this->signInAs(Role::TeamLead);
        $zone = Zone::factory()->create(['name' => 'Mer territoriale']);
        $agent = Agent::factory()->create();
        $asset = Asset::factory()->create();

        $this->schedule(['zone_ids' => [$zone->id], 'points_gps' => "5.10, -4.20\n4.80, -5.50", 'agent_ids' => [$agent->id], 'lead_agent_id' => $agent->id, 'asset_ids' => [$asset->id]])->assertRedirect();

        $mission = Mission::sole();
        $this->assertSame('MIS-'.now()->addDay()->year.'-0001', $mission->code);
        $this->assertSame(MissionStatus::Scheduled, $mission->status);
        $this->assertSame('Mer territoriale', $mission->zones[0]['name']);
        $this->assertSame(['latitude' => 5.1, 'longitude' => -4.2], $mission->points_gps[0]);
        $this->assertTrue($mission->participants()->sole()->is_mission_lead);
        $this->assertSame($asset->id, $mission->missionAssets()->sole()->asset_id);
        $this->assertSame($actor->id, Activity::where('event', 'missions.schedule')->sole()->causer_id);
    }

    #[Covers('UC18', partial: true)]
    #[Assumes('H-6-2')]
    public function test_by_default_only_the_director_authorizes_and_a_setting_lets_the_team_lead_authorize_a_type(): void
    {
        $mission = Mission::factory()->create();

        $this->signInAs(Role::TeamLead);
        $this->post(route('missions.authorize', $mission))->assertForbidden();
        $this->assertSame(MissionStatus::Scheduled, $mission->fresh()->status);

        config(['surveillance.missions.team_lead_authorizes.maritime' => true]);
        $teamLead = $this->signInAs(Role::TeamLead);
        $this->post(route('missions.authorize', $mission))->assertRedirect();

        $authorization = $mission->authorizations()->sole();
        $this->assertSame('team_lead', $authorization->authorizing_role->value);
        $this->assertSame($teamLead->id, $authorization->authorized_by);
        $this->assertSame(MissionStatus::Authorized, $mission->fresh()->status);
    }

    #[Assumes('H-6-2')]
    public function test_the_director_authorizes_every_type_and_a_mission_is_authorized_once(): void
    {
        $this->signInAs(Role::Director);
        $mission = Mission::factory()->create(['type' => 'aerial_drone']);

        $this->post(route('missions.authorize', $mission))->assertRedirect();
        $this->assertSame('director', $mission->authorizations()->sole()->authorizing_role->value);

        $this->post(route('missions.authorize', $mission))->assertSessionHasErrors('mission');
        $this->assertSame(1, $mission->authorizations()->count());
    }

    #[Covers('UC18', partial: true)]
    public function test_a_mission_cannot_start_before_it_is_authorized(): void
    {
        $this->signInAs(Role::TeamLead);
        $mission = Mission::factory()->create();

        $this->post(route('missions.start', $mission))->assertSessionHasErrors('mission');
        $this->assertSame(MissionStatus::Scheduled, $mission->fresh()->status);
    }

    #[Covers('UC18', partial: true)]
    public function test_the_encountered_unit_has_three_levels_and_an_inspected_unit_requires_the_e_inspection_link(): void
    {
        $this->signInAs(Role::TeamLead);
        $mission = Mission::factory()->completed()->create(['status' => MissionStatus::InProgress]);
        $vessel = Vessel::factory()->create();
        $inspection = ExternalInspection::factory()->create(['vessel_id' => $vessel->id]);

        $this->post(route('missions.units.store', $mission), ['level' => 'sighted', 'sighting_source' => 'radar', 'vessel_id' => $vessel->id, 'latitude' => 4.9, 'longitude' => -5.1])->assertRedirect();
        $this->post(route('missions.units.store', $mission), ['level' => 'approached', 'recorded_name' => 'PIROGUE 12', 'observed_activity' => 'Pêche à la ligne'])->assertRedirect();
        $this->post(route('missions.units.store', $mission), ['level' => 'inspected', 'vessel_id' => $vessel->id])->assertSessionHasErrors('inspection_id');
        $this->assertSame(2, $mission->observedUnits()->count());

        $this->post(route('missions.units.store', $mission), ['level' => 'inspected', 'vessel_id' => $vessel->id, 'inspection_id' => $inspection->id])->assertRedirect();

        $this->assertSame(['sighted', 'approached', 'inspected'], $mission->observedUnits()->get()->map(fn ($unit) => $unit->level->value)->all());
        $this->assertSame(2, VesselEvent::where('type', 'mission.unit_observed')->count());
    }

    public function test_the_database_refuses_an_inspected_unit_without_inspection(): void
    {
        $mission = Mission::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('observed_units')->insert(['mission_id' => $mission->id, 'level' => 'inspected', 'observed_at' => now()]);
    }

    #[Covers('UC18', partial: true)]
    public function test_a_unit_matching_a_registration_number_is_linked_to_the_vessel(): void
    {
        $this->signInAs(Role::TeamLead);
        $mission = Mission::factory()->create(['status' => MissionStatus::InProgress]);
        $vessel = Vessel::factory()->create(['registration_number' => 'AN-4242']);

        $this->post(route('missions.units.store', $mission), ['level' => 'approached', 'recorded_marks' => 'AN-4242'])->assertRedirect();

        $this->assertSame($vessel->id, $mission->observedUnits()->sole()->vessel_id);
    }

    #[Covers('UC18', partial: true)]
    #[Assumes('H-6-10')]
    public function test_a_gpx_track_is_stored_as_a_postgis_line_with_the_zones_it_crosses(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        $mission = Mission::factory()->create(['status' => MissionStatus::InProgress]);
        Zone::factory()->create(['name' => 'Mer territoriale', 'geometry' => ['type' => 'Polygon', 'coordinates' => [[[-5, 4], [-5, 6], [-3, 6], [-3, 4], [-5, 4]]]]]);
        Zone::factory()->create(['name' => 'Loin', 'geometry' => ['type' => 'Polygon', 'coordinates' => [[[10, 10], [10, 11], [11, 11], [11, 10], [10, 10]]]]]);
        $gpx = '<?xml version="1.0"?><gpx version="1.1" xmlns="http://www.topografix.com/GPX/1/1"><trk><trkseg><trkpt lat="5.0" lon="-4.5"></trkpt><trkpt lat="5.1" lon="-4.4"></trkpt><trkpt lat="5.2" lon="-4.3"></trkpt></trkseg></trk></gpx>';

        $this->post(route('missions.track.store', $mission), ['file' => UploadedFile::fake()->createWithContent('trace.gpx', $gpx)])->assertRedirect();

        $track = $mission->tracks()->sole();
        $this->assertSame(3, (int) DB::selectOne('select ST_NPoints(geometry) as total from patrol_tracks where id = ?', [$track->id])->total);
        $this->assertSame(['Mer territoriale'], collect($track->zones_crossed)->pluck('name')->all());
        $this->get(route('missions.show', $mission))->assertOk();
    }

    public function test_an_unreadable_gpx_file_is_rejected_and_stores_nothing(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        $mission = Mission::factory()->create(['status' => MissionStatus::InProgress]);

        $this->post(route('missions.track.store', $mission), ['file' => UploadedFile::fake()->createWithContent('trace.gpx', 'pas du xml')])->assertSessionHasErrors('file');
        $this->assertSame(0, $mission->tracks()->count());
    }

    #[Covers('UC18', partial: true)]
    #[Assumes('H-6-4')]
    public function test_the_review_is_computed_from_the_mission_data_never_typed(): void
    {
        $this->signInAs(Role::Director);
        $mission = Mission::factory()->create();
        $this->post(route('missions.authorize', $mission))->assertRedirect();
        $this->post(route('missions.start', $mission))->assertRedirect();

        $agent = Agent::factory()->create();
        $this->post(route('missions.participants.store', $mission), ['agent_id' => $agent->id])->assertRedirect();
        $this->post(route('missions.assets.store', $mission), ['asset_id' => Asset::factory()->create()->id])->assertRedirect();
        $this->put(route('missions.assets.usage', $mission->missionAssets()->sole()), ['nautical_miles' => 120.5, 'consumed_fuel_l' => 800])->assertRedirect();

        $compliant = ExternalInspection::factory()->create(['result' => 'compliant']);
        $nonCompliant = ExternalInspection::factory()->create(['result' => 'non_compliant']);
        $this->post(route('missions.units.store', $mission), ['level' => 'sighted'])->assertRedirect();
        $this->post(route('missions.units.store', $mission), ['level' => 'inspected', 'inspection_id' => $compliant->id])->assertRedirect();
        $this->post(route('missions.units.store', $mission), ['level' => 'inspected', 'inspection_id' => $nonCompliant->id])->assertRedirect();
        InfractionCase::factory()->create(['mission_id' => $mission->id]);

        $this->post(route('missions.complete', $mission))->assertRedirect();

        $review = $mission->fresh()->review;
        $this->assertSame(MissionStatus::Completed, $mission->fresh()->status);
        $this->assertSame(1, $review->agents_count);
        $this->assertSame('120.5', $review->nautical_miles);
        $this->assertSame('800.0', $review->fuel_l);
        $this->assertSame([1, 0, 2], [$review->sighted_units_count, $review->approached_units_count, $review->inspected_units_count]);
        $this->assertSame([1, 1], [$review->compliant_count, $review->non_compliant_count]);
        $this->assertSame(1, $review->infractions_count);
        $this->assertSame(0, $review->arrests_count);

        $this->post(route('missions.close', $mission))->assertRedirect();
        $this->assertSame(MissionStatus::Closed, $mission->fresh()->status);
        $this->post(route('missions.units.store', $mission), ['level' => 'sighted'])->assertSessionHasErrors('mission');
    }

    public function test_the_mission_page_renders_every_section(): void
    {
        $this->signInAs(Role::Director);
        $mission = Mission::factory()->completed()->create();

        $this->get(route('missions.show', $mission))->assertOk()->assertSee($mission->code);
        $this->get(route('missions.create'))->assertOk();
    }
}
