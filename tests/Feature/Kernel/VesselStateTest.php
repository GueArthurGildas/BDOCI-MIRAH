<?php

namespace Tests\Feature\Kernel;

use App\Enums\CurrentLicenseStatus;
use App\Models\License;
use App\Models\Position;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Support\Events\VesselStateBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VesselStateTest extends TestCase
{
    use RefreshDatabase;

    private function stateOf(Vessel $vessel): VesselCurrentState
    {
        return app(VesselStateBuilder::class)->refresh($vessel->id);
    }

    public function test_a_vessel_without_license_has_no_license_provided(): void
    {
        $state = $this->stateOf(Vessel::factory()->create());

        $this->assertSame(CurrentLicenseStatus::NotProvided, $state->license_status);
        $this->assertNull($state->license_expires_on);
    }

    public function test_the_license_status_follows_the_latest_license(): void
    {
        $active = Vessel::factory()->create();
        License::factory()->for($active)->expiringIn(200)->create();
        $expiring = Vessel::factory()->create();
        License::factory()->for($expiring)->expiringIn(10)->create();
        $expired = Vessel::factory()->create();
        License::factory()->for($expired)->expired()->create();
        $suspended = Vessel::factory()->create();
        License::factory()->for($suspended)->suspended()->create();

        $this->assertSame(CurrentLicenseStatus::Active, $this->stateOf($active)->license_status);
        $this->assertSame(CurrentLicenseStatus::Active, $this->stateOf($expiring)->license_status);
        $this->assertSame(CurrentLicenseStatus::Expired, $this->stateOf($expired)->license_status);
        $this->assertSame(CurrentLicenseStatus::Suspended, $this->stateOf($suspended)->license_status);
        $this->assertSame(today()->addDays(200)->toDateString(), $this->stateOf($active)->license_expires_on->toDateString());
    }

    public function test_silence_is_only_computed_for_vessels_with_an_installed_beacon(): void
    {
        $monitored = TrackingDevice::factory()->create();
        Position::factory()->for($monitored->vessel)->create(['recorded_at' => now()->subHours(5)]);
        $unmonitored = Vessel::factory()->create();
        Position::factory()->for($unmonitored)->create(['recorded_at' => now()->subHours(5)]);

        $this->assertEqualsWithDelta(5.0, $this->stateOf($monitored->vessel)->vms_silence_hours, 0.1);
        $this->assertNull($this->stateOf($unmonitored)->vms_silence_hours);
        $this->assertNotNull($this->stateOf($unmonitored)->last_position_at);
    }

    public function test_rebuilding_gives_the_same_state_as_incremental_refreshes(): void
    {
        $vessels = Vessel::factory()->count(3)->create();
        License::factory()->for($vessels[0])->expired()->create();
        License::factory()->for($vessels[1])->expiringIn(40)->create();
        $vessels->each(fn (Vessel $vessel) => $this->stateOf($vessel));
        $incremental = VesselCurrentState::orderBy('vessel_id')->get(['license_status', 'license_expires_on'])->toArray();

        VesselCurrentState::query()->delete();
        $this->artisan('vessels:rebuild-state')->expectsOutputToContain('3 vessel states rebuilt')->assertSuccessful();

        $this->assertSame($incremental, VesselCurrentState::orderBy('vessel_id')->get(['license_status', 'license_expires_on'])->toArray());
    }
}
