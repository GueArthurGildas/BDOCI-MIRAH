<?php

namespace Tests\Feature\Vigilance;

use App\Enums\CurrentLicenseStatus;
use App\Enums\EezCheckResult;
use App\Enums\InfractionStatus;
use App\Enums\VesselStatusLight;
use App\Enums\WatchOrigin;
use App\Models\CheckCounter;
use App\Models\EezDeclaration;
use App\Models\EezDeclarationCheck;
use App\Models\Infraction;
use App\Models\License;
use App\Models\Position;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Models\VmsFailure;
use App\Models\WatchEntry;
use App\Support\Events\VesselStateBuilder;
use App\Support\Vessels\StatusLight;
use App\Support\Vessels\StatusLightFacts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Assumes('H-8-1')]
class StatusLightTest extends TestCase
{
    use RefreshDatabase;

    private function assess(Vessel $vessel): VesselStatusLight
    {
        return StatusLight::assess(StatusLightFacts::gather($vessel->fresh()))->level;
    }

    #[Covers('UC25', partial: true)]
    public function test_a_vessel_with_a_valid_license_and_nothing_else_is_compliant(): void
    {
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expiringIn(200)->create();

        $this->assertSame(VesselStatusLight::Compliant, $this->assess($vessel));
    }

    #[Covers('UC25', partial: true)]
    public function test_a_license_close_to_expiry_puts_the_vessel_to_watch(): void
    {
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expiringIn(10)->create();

        $this->assertSame(VesselStatusLight::ToWatch, $this->assess($vessel));
    }

    #[Covers('UC25', partial: true)]
    public function test_enhanced_monitoring_and_an_ongoing_failure_put_the_vessel_to_watch(): void
    {
        $monitored = Vessel::factory()->create();
        WatchEntry::factory()->create(['vessel_id' => $monitored->id, 'origin' => WatchOrigin::EnhancedMonitoring, 'status' => 'open']);
        $failing = Vessel::factory()->create();
        VmsFailure::factory()->create(['vessel_id' => $failing->id, 'ended_at' => null]);

        $this->assertSame(VesselStatusLight::ToWatch, $this->assess($monitored));
        $this->assertSame(VesselStatusLight::ToWatch, $this->assess($failing));
    }

    #[Covers('UC25', partial: true)]
    public function test_an_expired_license_makes_the_vessel_suspect(): void
    {
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expired()->create();

        $this->assertSame(VesselStatusLight::Suspect, $this->assess($vessel));
    }

    #[Covers('UC25', partial: true)]
    public function test_a_silent_beacon_without_failure_makes_the_vessel_suspect(): void
    {
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(10)]);

        $this->assertSame(VesselStatusLight::Suspect, $this->assess($device->vessel));
    }

    #[Covers('UC25', partial: true)]
    public function test_repeated_checks_make_the_vessel_suspect(): void
    {
        $vessel = Vessel::factory()->create();
        CheckCounter::factory()->create(['vessel_id' => $vessel->id, 'checks_count' => 3]);

        $this->assertSame(VesselStatusLight::Suspect, $this->assess($vessel));
    }

    #[Covers('UC25', partial: true)]
    public function test_an_unexplained_eez_variance_makes_the_vessel_suspect(): void
    {
        $vessel = Vessel::factory()->create();
        $declaration = EezDeclaration::factory()->create(['vessel_id' => $vessel->id, 'received_at' => now()->subDays(3)]);
        EezDeclarationCheck::factory()->create(['eez_declaration_id' => $declaration->id, 'result' => EezCheckResult::Ko]);

        $this->assertSame(VesselStatusLight::Suspect, $this->assess($vessel));
    }

    #[Covers('UC25', partial: true)]
    public function test_an_open_infraction_case_puts_the_vessel_in_violation_above_every_other_fact(): void
    {
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expired()->create();
        Infraction::factory()->create(['vessel_id' => $vessel->id, 'status' => InfractionStatus::UnderInvestigation]);

        $this->assertSame(VesselStatusLight::InViolation, $this->assess($vessel));
    }

    #[Covers('UC25', partial: true)]
    public function test_a_closed_infraction_case_does_not_count(): void
    {
        $vessel = Vessel::factory()->create();
        Infraction::factory()->create(['vessel_id' => $vessel->id, 'status' => InfractionStatus::Closed]);

        $this->assertSame(VesselStatusLight::Compliant, $this->assess($vessel));
    }

    public function test_the_current_state_stores_the_status_light_and_its_facts(): void
    {
        $vessel = Vessel::factory()->create();
        Infraction::factory()->create(['vessel_id' => $vessel->id, 'status' => InfractionStatus::Reported]);

        $state = app(VesselStateBuilder::class)->refresh($vessel->id);

        $this->assertSame('in_violation', $state->status_light);
        $this->assertSame(1, $state->open_infractions_count);
        $this->assertSame(CurrentLicenseStatus::NotProvided, $state->license_status);
    }

    public function test_rebuilding_the_states_gives_the_same_status_lights_as_incremental_refreshes(): void
    {
        $vessels = Vessel::factory()->count(3)->create();
        License::factory()->for($vessels[0])->expired()->create();
        Infraction::factory()->create(['vessel_id' => $vessels[1]->id, 'status' => InfractionStatus::Reported]);
        $vessels->each(fn (Vessel $vessel) => app(VesselStateBuilder::class)->refresh($vessel->id));
        $incremental = VesselCurrentState::orderBy('vessel_id')->pluck('status_light', 'vessel_id')->all();

        VesselCurrentState::query()->delete();
        $this->artisan('vessels:rebuild-state')->assertSuccessful();

        $this->assertSame($incremental, VesselCurrentState::orderBy('vessel_id')->pluck('status_light', 'vessel_id')->all());
    }
}
