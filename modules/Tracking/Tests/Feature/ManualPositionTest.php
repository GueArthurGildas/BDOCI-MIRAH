<?php

namespace Modules\Tracking\Tests\Feature;

use App\Enums\BeaconStatus;
use App\Enums\PositionLoadingSource;
use App\Enums\SlotTestResult;
use App\Enums\TrackingSystem;
use App\Models\ControlSlot;
use App\Models\Position;
use App\Models\SlotPosition;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\Zone;
use App\Models\ZoneCount;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class ManualPositionTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC05', partial: true)]
    #[Assumes('H-10-1')]
    public function test_a_position_entered_by_hand_counts_in_the_zone_situation_without_making_the_beacon_emit(): void
    {
        $this->signInAs(Role::Agent);
        $zone = Zone::factory()->create(['name' => 'Port de San Pedro']);
        $device = TrackingDevice::factory()->create();
        $vessel = $device->vessel;
        Position::factory()->for($vessel)->create(['recorded_at' => now()->subHours(9), 'system' => TrackingSystem::Vms]);

        $this->get(route('tracking.positions.create'))->assertOk();
        $this->post(route('tracking.positions.store'), [
            'vessel_id' => $vessel->id,
            'recorded_at' => now()->subMinutes(10)->format('Y-m-d\TH:i'),
            'zone_id' => $zone->id,
        ])->assertRedirect(route('tracking.zones'));

        $position = Position::where('system', TrackingSystem::Manual->value)->sole();
        $this->assertSame($zone->id, $position->zone_id);
        $this->assertNotNull($position->latitude);
        $this->assertSame(1, VesselEvent::where('type', 'position.manual_recorded')->count());

        $this->get(route('tracking.zones'))->assertOk()->assertViewHas('vesselCountByZone', fn ($counts) => $counts->get($zone->id) === 1);
        $this->assertSame(BeaconStatus::Silent, $vessel->fresh()->beaconStatus());
    }

    #[Covers('UC05', partial: true)]
    #[Assumes('H-10-1')]
    public function test_a_manual_position_is_added_to_a_slot_and_its_tests_are_computed(): void
    {
        $this->signInAs(Role::Agent);
        $zone = Zone::factory()->create();
        $slot = ControlSlot::factory()->create();
        $vessel = Vessel::factory()->create();
        $centre = json_decode(json_encode($zone->geometry), true)['coordinates'][0][0];

        $this->get(route('tracking.positions.create', ['control_slot_id' => $slot->id]))->assertOk()->assertSee('name="control_slot_id"', false);
        $this->post(route('tracking.positions.store'), [
            'control_slot_id' => $slot->id,
            'vessel_id' => $vessel->id,
            'recorded_at' => now()->format('Y-m-d\TH:i'),
            'latitude' => $centre[1] + 0.05,
            'longitude' => $centre[0] + 0.05,
            'speed_knots' => 3,
            'activity' => 'fishing',
        ])->assertRedirect(route('tracking.slots.show', $slot));

        $row = SlotPosition::sole();
        $this->assertSame(PositionLoadingSource::Manual, $row->loading_source);
        $this->assertSame($zone->id, $row->zone_id);
        $this->assertSame(SlotTestResult::Ko, $row->emission_test);
        $this->assertSame(SlotTestResult::Ok, $row->speed_activity_test);
        $this->assertSame(1, ZoneCount::where('control_slot_id', $slot->id)->where('zone_id', $zone->id)->value('vessels_count'));
    }

    public function test_a_manual_position_needs_coordinates_or_a_zone_and_the_right_to_record(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post(route('tracking.positions.store'), ['vessel_id' => $vessel->id, 'recorded_at' => now()->format('Y-m-d\TH:i')])->assertSessionHasErrors('zone_id');
        $this->post(route('tracking.positions.store'), ['vessel_id' => $vessel->id, 'recorded_at' => now()->addDay()->format('Y-m-d\TH:i'), 'latitude' => 5, 'longitude' => -4])->assertSessionHasErrors('recorded_at');

        $this->signInAs(Role::Admin);
        $this->post(route('tracking.positions.store'), ['vessel_id' => $vessel->id, 'recorded_at' => now()->format('Y-m-d\TH:i'), 'latitude' => 5, 'longitude' => -4])->assertForbidden();

        $this->assertSame(0, Position::count());
    }
}
