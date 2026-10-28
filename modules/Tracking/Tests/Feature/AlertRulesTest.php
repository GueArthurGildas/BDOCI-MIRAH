<?php

namespace Modules\Tracking\Tests\Feature;

use App\Enums\AlertRuleType;
use App\Enums\AlertStatus;
use App\Enums\ZoneType;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\BeaconAssignment;
use App\Models\Position;
use App\Models\TrackingDevice;
use App\Models\VmsCutoffAuthorization;
use App\Queries\SilentBeacons;
use App\Support\Authorization\Role;
use App\Support\Vms\AlertRuleEvaluator;
use App\Support\Vms\SilenceThreshold;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\Concerns\CreatesZones;
use Tests\TestCase;

class AlertRulesTest extends TestCase
{
    use CreatesZones, RefreshDatabase;

    #[Covers('UC06', 'RG04', partial: true)]
    public function test_the_default_silence_threshold_is_four_hours(): void
    {
        $this->assertSame(4.0, app(SilenceThreshold::class)->hours());
    }

    #[Covers('UC06', partial: true)]
    #[Assumes('H-3b-7')]
    public function test_the_threshold_comes_from_the_active_silence_rule(): void
    {
        AlertRule::factory()->create(['code' => 'silence', 'type' => AlertRuleType::Silence, 'threshold_hours' => 6]);

        $this->assertSame(6.0, app(SilenceThreshold::class)->hours());
    }

    #[Covers('UC06', partial: true)]
    public function test_a_beacon_silent_for_more_than_the_threshold_raises_one_alert(): void
    {
        $rule = AlertRule::factory()->create(['type' => AlertRuleType::Silence, 'threshold_hours' => 4]);
        $silent = TrackingDevice::factory()->create();
        Position::factory()->for($silent->vessel)->create(['recorded_at' => now()->subHours(5)]);
        $emitting = TrackingDevice::factory()->create();
        Position::factory()->for($emitting->vessel)->create(['recorded_at' => now()->subHours(3)]);

        app(AlertRuleEvaluator::class)->evaluate();
        app(AlertRuleEvaluator::class)->evaluate();

        $this->assertSame(1, Alert::count());
        $alert = Alert::first();
        $this->assertSame($silent->vessel_id, $alert->vessel_id);
        $this->assertSame($rule->id, $alert->alert_rule_id);
        $this->assertSame(AlertStatus::Open, $alert->status);
    }

    #[Covers('UC06', partial: true)]
    public function test_the_alert_closes_itself_when_positions_come_back(): void
    {
        AlertRule::factory()->create(['type' => AlertRuleType::Silence, 'threshold_hours' => 4]);
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(6)]);
        app(AlertRuleEvaluator::class)->evaluate();

        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subMinutes(5)]);
        app(AlertRuleEvaluator::class)->evaluate();

        $this->assertSame(AlertStatus::Closed, Alert::first()->status);
    }

    #[Covers('RG04', partial: true)]
    public function test_an_authorized_technical_stop_raises_no_silence_alert(): void
    {
        AlertRule::factory()->create(['type' => AlertRuleType::Silence, 'threshold_hours' => 4]);
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(10)]);
        VmsCutoffAuthorization::factory()->create(['vessel_id' => $device->vessel_id, 'started_at' => now()->subDay(), 'ended_at' => now()->addDay()]);

        app(AlertRuleEvaluator::class)->evaluate();

        $this->assertSame(0, Alert::count());
    }

    #[Covers('RG05', partial: true)]
    public function test_vessels_with_an_active_assignment_are_monitored_without_a_legacy_device(): void
    {
        AlertRule::factory()->create(['type' => AlertRuleType::Silence, 'threshold_hours' => 4]);
        $assignment = BeaconAssignment::factory()->create();
        Position::factory()->for($assignment->vessel)->create(['recorded_at' => now()->subHours(9)]);

        app(AlertRuleEvaluator::class)->evaluate();

        $this->assertSame($assignment->vessel_id, Alert::first()->vessel_id);
    }

    #[Covers('UC06', partial: true)]
    public function test_a_vessel_inside_a_restricted_zone_raises_a_geofencing_alert(): void
    {
        $zone = $this->squareZone('AMP Test', ZoneType::Restricted, 5.0, -4.0, 0.1);
        AlertRule::factory()->create(['code' => 'geo', 'type' => AlertRuleType::Geofencing, 'threshold_hours' => null]);
        $inside = Position::factory()->create(['latitude' => 5.0, 'longitude' => -4.0, 'recorded_at' => now()->subMinutes(5)]);
        Position::factory()->create(['latitude' => 3.0, 'longitude' => -4.0, 'recorded_at' => now()->subMinutes(5)]);

        app(AlertRuleEvaluator::class)->evaluate();

        $this->assertSame(1, Alert::count());
        $this->assertSame([$inside->vessel_id, $zone->id], [Alert::first()->vessel_id, Alert::first()->zone_id]);
    }

    #[Covers('UC06', partial: true)]
    public function test_a_vessel_above_the_speed_limit_raises_a_speed_alert(): void
    {
        AlertRule::factory()->create(['code' => 'speed', 'type' => AlertRuleType::Speed, 'threshold_hours' => null, 'parameters' => ['max_knots' => 20]]);
        Position::factory()->create(['speed_knots' => 31, 'recorded_at' => now()->subMinutes(5)]);
        Position::factory()->create(['speed_knots' => 8, 'recorded_at' => now()->subMinutes(5)]);

        app(AlertRuleEvaluator::class)->evaluate();

        $this->assertSame(1, Alert::count());
    }

    #[Covers('UC06', partial: true)]
    #[Assumes('H-3b-7')]
    public function test_the_administrator_edits_the_threshold_and_it_applies_to_beacon_status(): void
    {
        $rule = AlertRule::factory()->create(['type' => AlertRuleType::Silence, 'threshold_hours' => 4]);
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(5)]);
        $this->signInAs(Role::Admin);

        $this->put(route('tracking.rules.update', $rule), ['type' => 'silence', 'threshold_hours' => 8, 'is_active' => 1])->assertRedirect(route('tracking.rules.index'));

        $this->assertEquals(8, $rule->fresh()->threshold_hours);
        $this->assertSame(0, SilentBeacons::vessels()->count());
    }

    public function test_agents_cannot_edit_alert_rules(): void
    {
        $rule = AlertRule::factory()->create();
        $this->signInAs(Role::Agent);

        $this->put(route('tracking.rules.update', $rule), ['type' => 'silence', 'threshold_hours' => 8])->assertForbidden();
        $this->get(route('tracking.rules.index'))->assertForbidden();
    }
}
