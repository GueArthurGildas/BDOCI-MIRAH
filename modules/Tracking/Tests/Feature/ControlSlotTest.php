<?php

namespace Modules\Tracking\Tests\Feature;

use App\Enums\SlotResult;
use App\Enums\SlotTestResult;
use App\Enums\SweepAnomalyType;
use App\Enums\ZoneType;
use App\Models\ConsistencyRule;
use App\Models\ControlSlot;
use App\Models\License;
use App\Models\Position;
use App\Models\ReferenceListValue;
use App\Models\SlotPosition;
use App\Models\SurveillanceObservation;
use App\Models\SweepAnomaly;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\ZoneCount;
use App\Models\ZoneRule;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Vms\AssistedTests;
use App\Support\Vms\LocalityResolver;
use App\Support\Vms\PositionFeed;
use App\Support\Vms\ThemisLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\Concerns\CreatesZones;
use Tests\TestCase;

class ControlSlotTest extends TestCase
{
    use CreatesZones, RefreshDatabase;

    private function monitoredVessel(array $position): Vessel
    {
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create($position);

        return $device->vessel;
    }

    #[Covers('UC05', 'RG06', partial: true)]
    #[Assumes('H-3b-1')]
    public function test_opening_a_slot_loads_the_snapshot_of_monitored_vessels_through_the_feed(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $emitting = $this->monitoredVessel(['recorded_at' => now()->subMinutes(15), 'speed_knots' => 3]);
        $silent = $this->monitoredVessel(['recorded_at' => now()->subHours(9)]);
        Position::factory()->create(['recorded_at' => now()->subMinutes(5)]);

        $this->post(route('tracking.slots.store'))->assertRedirect();

        $slot = ControlSlot::first();
        $this->assertSame($agent->id, $slot->operator_user_id);
        $this->assertSame(2, $slot->slotPositions()->count());
        $this->assertSame('api', $slot->slotPositions()->first()->loading_source->value);
        $this->assertSame(SlotTestResult::Ok, SlotPosition::where('vessel_id', $emitting->id)->first()->emission_test);
        $this->assertSame(SlotTestResult::Ko, SlotPosition::where('vessel_id', $silent->id)->first()->emission_test);
        $this->assertInstanceOf(PositionFeed::class, app(PositionFeed::class));
    }

    #[Covers('UC05', 'RG06', partial: true)]
    #[Assumes('H-3b-4')]
    public function test_the_speed_and_activity_test_follows_the_consistency_rule(): void
    {
        ConsistencyRule::factory()->create(['code' => AssistedTests::SPEED_ACTIVITY_RULE, 'parameters' => ['fishing_max_knots' => 5]]);
        $this->signInAs(Role::Agent);
        $coherent = $this->monitoredVessel(['recorded_at' => now()->subMinutes(10), 'speed_knots' => 3, 'activity' => 'fishing']);
        $incoherent = $this->monitoredVessel(['recorded_at' => now()->subMinutes(10), 'speed_knots' => 9, 'activity' => 'fishing']);
        $unknown = $this->monitoredVessel(['recorded_at' => now()->subMinutes(10), 'speed_knots' => 9, 'activity' => null]);

        $this->post(route('tracking.slots.store'));

        $result = fn (Vessel $vessel) => SlotPosition::where('vessel_id', $vessel->id)->first()->speed_activity_test;
        $this->assertSame(SlotTestResult::Ok, $result($coherent));
        $this->assertSame(SlotTestResult::Ko, $result($incoherent));
        $this->assertSame(SlotTestResult::ToConfirm, $result($unknown));
    }

    #[Covers('UC05', 'RG06', partial: true)]
    #[Assumes('H-3b-4')]
    public function test_a_trajectory_through_a_restricted_zone_fails_the_trajectory_test(): void
    {
        $this->squareZone('AMP', ZoneType::Restricted, 5.0, -4.0, 0.2);
        $this->signInAs(Role::Agent);
        $crossing = $this->monitoredVessel(['recorded_at' => now()->subMinutes(10), 'latitude' => 5.0, 'longitude' => -4.0]);
        $clean = $this->monitoredVessel(['recorded_at' => now()->subMinutes(10), 'latitude' => 4.0, 'longitude' => -6.0]);

        $this->post(route('tracking.slots.store'));

        $this->assertSame(SlotTestResult::Ko, SlotPosition::where('vessel_id', $crossing->id)->first()->trajectory_test);
        $this->assertSame(SlotTestResult::Ok, SlotPosition::where('vessel_id', $clean->id)->first()->trajectory_test);
    }

    #[Covers('UC05', 'RG06', partial: true)]
    #[Assumes('H-3b-4', 'H-3b-5')]
    public function test_a_trawler_inside_the_zone_rule_distance_from_the_coast_fails_the_trajectory_test(): void
    {
        $eez = $this->squareZone('ZEE', ZoneType::ExclusiveEconomicZone, 4.5, -5.0, 2.0);
        ZoneRule::factory()->create(['zone_id' => $eez->id, 'segment' => 'trawl', 'nautical_miles' => 12]);
        ConsistencyRule::factory()->create(['code' => AssistedTests::TRAJECTORY_RULE, 'parameters' => [
            'window_hours' => 6,
            'coastline' => ['type' => 'LineString', 'coordinates' => [[-6.0, 5.0], [-4.0, 5.0]]],
        ]]);
        $this->signInAs(Role::Agent);
        $near = $this->monitoredVessel(['recorded_at' => now()->subMinutes(10), 'latitude' => 4.95, 'longitude' => -5.0, 'zone_id' => $eez->id]);
        $near->update(['type' => 'trawler']);
        $far = $this->monitoredVessel(['recorded_at' => now()->subMinutes(10), 'latitude' => 4.5, 'longitude' => -5.0, 'zone_id' => $eez->id]);
        $far->update(['type' => 'trawler']);

        $this->post(route('tracking.slots.store'));

        $this->assertSame(SlotTestResult::Ko, SlotPosition::where('vessel_id', $near->id)->first()->trajectory_test);
        $this->assertSame(SlotTestResult::Ok, SlotPosition::where('vessel_id', $far->id)->first()->trajectory_test);
    }

    #[Covers('UC05', partial: true)]
    #[Assumes('H-3b-2')]
    public function test_vessels_are_counted_by_zone_and_by_counting_locality(): void
    {
        $abidjan = $this->squareZone('Abidjan', ZoneType::PortArea, 5.25, -4.0, 0.1);
        $tabou = $this->squareZone('Tabou', ZoneType::PortArea, 4.4, -7.3, 0.1);
        $this->signInAs(Role::Agent);
        foreach ([$abidjan, $abidjan, $tabou] as $zone) {
            $this->monitoredVessel(['recorded_at' => now()->subMinutes(10), 'zone_id' => $zone->id, 'latitude' => 5.25, 'longitude' => -4.0]);
        }

        $this->post(route('tracking.slots.store'));

        $slot = ControlSlot::first();
        $this->assertSame(2, ZoneCount::where('control_slot_id', $slot->id)->where('zone_id', $abidjan->id)->value('vessels_count'));
        $abidjanLocality = ReferenceListValue::where('code', 'abidjan')->value('id');
        $this->assertSame(2, $slot->slotPositions()->where('locality_id', $abidjanLocality)->count());
        $this->assertSame(0, $slot->slotPositions()->where('zone_id', $tabou->id)->whereNotNull('locality_id')->count());
    }

    #[Covers('UC05', partial: true)]
    #[Assumes('H-3b-2')]
    public function test_the_counting_localities_are_an_editable_reference_list_seeded_with_three_ports(): void
    {
        $list = app(LocalityResolver::class)->ensure();

        $this->assertSame(['abidjan', 'san-pedro', 'sassandra'], $list->values()->orderBy('sort_order')->pluck('code')->all());

        $list->values()->where('code', 'abidjan')->update(['is_active' => false]);
        $zone = $this->squareZone('Abidjan', ZoneType::PortArea, 5.25, -4.0, 0.1);

        $this->assertNull(app(LocalityResolver::class)->idFor($zone->id));
    }

    #[Covers('UC05', 'RG06', partial: true)]
    public function test_the_operator_confirms_or_corrects_the_tests_and_the_result_follows(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = $this->monitoredVessel(['recorded_at' => now()->subMinutes(10)]);
        $this->post(route('tracking.slots.store'));
        $slot = ControlSlot::first();
        $position = $slot->slotPositions()->first();

        $this->post(route('tracking.slots.confirm', [$slot, $position]), ['emission_test' => 'ok', 'speed_activity_test' => 'ko', 'trajectory_test' => 'ok'])->assertRedirect();

        $position->refresh();
        $this->assertSame(SlotResult::Suspect, $position->result);
        $this->assertSame($agent->id, $position->confirmed_by);
        $this->assertTrue(VesselEvent::where('vessel_id', $vessel->id)->where('type', 'slot_position.confirmed')->exists());

        $this->post(route('tracking.slots.confirm', [$slot, $position]), ['emission_test' => 'ok', 'speed_activity_test' => 'ok', 'trajectory_test' => 'ok']);
        $this->assertSame(SlotResult::Normal, $position->fresh()->result);
    }

    #[Covers('UC05', partial: true)]
    public function test_a_suspect_position_links_to_the_observation_form_with_the_slot_position(): void
    {
        $this->signInAs(Role::Agent);
        $this->monitoredVessel(['recorded_at' => now()->subMinutes(10)]);
        $this->post(route('tracking.slots.store'));
        $slot = ControlSlot::first();
        $position = $slot->slotPositions()->first();
        $this->post(route('tracking.slots.confirm', [$slot, $position]), ['emission_test' => 'ko', 'speed_activity_test' => 'ok', 'trajectory_test' => 'ok']);

        $this->get(route('tracking.slots.show', $slot))->assertOk()->assertSee('slot_position_id='.$position->id, false);

        $this->post(route('watch.observations.store'), ['vessel_id' => $position->vessel_id, 'slot_position_id' => $position->id, 'body' => 'Silent beacon'])->assertRedirect();
        $this->assertSame($position->id, SurveillanceObservation::first()->slot_position_id);
    }

    #[Covers('UC05', partial: true)]
    public function test_a_sweep_lists_non_emitting_vessels_and_licensed_vessels_absent_from_the_feed(): void
    {
        $this->signInAs(Role::Agent);
        $silent = $this->monitoredVessel(['recorded_at' => now()->subHours(9)]);
        $absent = Vessel::factory()->create();
        License::factory()->for($absent)->create();
        $this->post(route('tracking.slots.store'));
        $slot = ControlSlot::first();

        $this->post(route('tracking.slots.sweep', $slot))->assertRedirect();

        $sweep = $slot->sweeps()->first();
        $this->assertSame([1, 1], [$sweep->vessels_count, $sweep->non_emitting_count]);
        $this->assertSame($silent->id, SweepAnomaly::where('type', SweepAnomalyType::NonEmitting)->value('vessel_id'));
        $this->assertSame($absent->id, SweepAnomaly::where('type', SweepAnomalyType::LicensedAbsentThemis)->value('vessel_id'));
    }

    #[Covers('UC22')]
    #[Assumes('H-3b-1')]
    public function test_the_themis_link_is_built_from_the_template_or_falls_back_to_copying_the_identifier(): void
    {
        $vessel = Vessel::factory()->create(['imo_number' => 'IMO1234567']);
        $themis = app(ThemisLink::class);

        $this->assertNull($themis->url($vessel));
        $this->assertSame('IMO1234567', $themis->identifier($vessel));

        config(['surveillance.vms.themis_vessel_url' => 'https://themis.example.test/vessels/{identifier}']);
        $this->assertSame('https://themis.example.test/vessels/IMO1234567', $themis->url($vessel));

        $this->signInAs(Role::Agent);
        $this->monitoredVessel(['recorded_at' => now()->subMinutes(10)]);
        $this->post(route('tracking.slots.store'));
        $this->get(route('tracking.slots.show', ControlSlot::first()))->assertOk()->assertSee('themis.example.test', false);
    }

    public function test_slots_need_the_create_permission_to_open_and_the_view_permission_to_read(): void
    {
        $this->signInWithPermissions(Permission::TrackingView);

        $this->get(route('tracking.slots.index'))->assertOk();
        $this->post(route('tracking.slots.store'))->assertForbidden();
        $this->assertSame(0, ControlSlot::count());
    }
}
