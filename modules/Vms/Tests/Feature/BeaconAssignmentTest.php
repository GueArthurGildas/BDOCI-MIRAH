<?php

namespace Modules\Vms\Tests\Feature;

use App\Enums\TrackingSystem;
use App\Models\Beacon;
use App\Models\BeaconAssignment;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\VesselReportingObligation;
use App\Support\Authorization\Role;
use App\Support\Vms\BeaconCompliance;
use App\Support\Vms\BeaconMirror;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class BeaconAssignmentTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('RG05', 'UC04', partial: true)]
    public function test_replacing_a_beacon_closes_the_previous_assignment_and_keeps_the_history(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        [$old, $new] = Beacon::factory(2)->create();

        $this->post(route('vms.beacons.assign'), ['vessel_id' => $vessel->id, 'beacon_id' => $old->id, 'started_on' => '2026-01-10'])->assertRedirect();
        $this->post(route('vms.beacons.assign'), ['vessel_id' => $vessel->id, 'beacon_id' => $new->id, 'started_on' => '2026-06-01', 'replacement_reason' => 'Faulty modem'])->assertRedirect();

        $history = BeaconAssignment::where('vessel_id', $vessel->id)->orderBy('started_on')->get();
        $this->assertSame(2, $history->count());
        $this->assertSame('2026-06-01', $history[0]->ended_on->toDateString());
        $this->assertNull($history[1]->ended_on);
        $this->assertSame('Faulty modem', $history[1]->replacement_reason);
        $this->assertSame(1, BeaconAssignment::active()->where('vessel_id', $vessel->id)->count());
        $this->assertSame(2, VesselEvent::where('vessel_id', $vessel->id)->where('type', 'beacon.assigned')->where('user_id', $agent->id)->count());
    }

    #[Covers('RG05', partial: true)]
    public function test_the_database_refuses_two_active_assignments_for_the_same_beacon_or_vessel(): void
    {
        $assignment = BeaconAssignment::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        BeaconAssignment::factory()->create(['beacon_id' => $assignment->beacon_id]);
    }

    #[Covers('RG05', partial: true)]
    public function test_a_beacon_in_use_on_another_vessel_cannot_be_assigned(): void
    {
        $this->signInAs(Role::Agent);
        $assignment = BeaconAssignment::factory()->create();
        $other = Vessel::factory()->create();

        $this->post(route('vms.beacons.assign'), ['vessel_id' => $other->id, 'beacon_id' => $assignment->beacon_id])->assertSessionHasErrors('beacon_id');

        $this->assertSame(1, BeaconAssignment::count());
    }

    #[Covers('RG03', 'UC04')]
    public function test_a_vessel_subject_to_vms_without_an_assigned_beacon_is_an_anomaly(): void
    {
        $equipped = BeaconAssignment::factory()->create()->vessel;
        $missing = Vessel::factory()->create();
        $notSubject = Vessel::factory()->create();
        foreach ([$equipped, $missing] as $vessel) {
            VesselReportingObligation::factory()->create(['vessel_id' => $vessel->id, 'requires_vms' => true]);
        }
        VesselReportingObligation::factory()->create(['vessel_id' => $notSubject->id, 'requires_vms' => false]);

        $this->assertSame([$missing->id], app(BeaconCompliance::class)->vesselsWithoutBeacon()->pluck('id')->all());

        $this->signInAs(Role::Agent);
        $this->get(route('vms.beacons.index'))->assertOk()->assertSee($missing->name);
    }

    #[Covers('RG05', partial: true)]
    #[Assumes('H-3b-7')]
    public function test_assignments_are_projected_onto_the_legacy_tracking_devices(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        [$old, $new] = Beacon::factory(2)->create();

        $this->post(route('vms.beacons.assign'), ['vessel_id' => $vessel->id, 'beacon_id' => $old->id, 'started_on' => '2026-01-10']);
        $this->post(route('vms.beacons.assign'), ['vessel_id' => $vessel->id, 'beacon_id' => $new->id, 'started_on' => '2026-06-01']);

        $this->assertNotNull(TrackingDevice::where('identifier', $old->serial_number)->first()->removed_at);
        $this->assertNull(TrackingDevice::where('identifier', $new->serial_number)->first()->removed_at);
        $this->assertTrue($vessel->fresh()->trackingDevices()->whereNull('removed_at')->exists());
    }

    #[Assumes('H-3b-7')]
    public function test_legacy_devices_are_imported_as_beacons_and_assignments_once(): void
    {
        $device = TrackingDevice::factory()->create();
        TrackingDevice::factory()->create(['system' => TrackingSystem::Ais]);

        $this->assertSame(1, app(BeaconMirror::class)->importLegacyDevices());
        $this->assertSame(0, app(BeaconMirror::class)->importLegacyDevices());

        $this->assertSame($device->vessel_id, BeaconAssignment::first()->vessel_id);
    }

    public function test_only_vms_managers_create_beacons_and_obligations(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post(route('vms.beacons.store'), ['serial_number' => 'X-1'])->assertForbidden();
        $this->post(route('vms.obligations.store'), ['vessel_id' => $vessel->id, 'requires_vms' => 1])->assertForbidden();

        $this->signInAs(Role::TeamLead);
        $this->post(route('vms.beacons.store'), ['serial_number' => 'X-1', 'modem' => 'gprs'])->assertRedirect();
        $this->post(route('vms.obligations.store'), ['vessel_id' => $vessel->id, 'requires_vms' => 1, 'requires_ers' => 0])->assertRedirect();

        $this->assertTrue(Beacon::where('serial_number', 'X-1')->exists());
        $this->assertTrue(VesselReportingObligation::where('vessel_id', $vessel->id)->first()->requires_vms);
    }

    public function test_administrators_cannot_open_the_vms_pages(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('vms.beacons.index'))->assertForbidden();
    }
}
