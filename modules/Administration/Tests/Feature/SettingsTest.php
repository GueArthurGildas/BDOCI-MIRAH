<?php

namespace Modules\Administration\Tests\Feature;

use App\Models\Position;
use App\Models\Setting;
use App\Models\TrackingDevice;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC24', partial: true)]
class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'notifications_poll_seconds' => 30,
            'notifications_toast_seconds' => 8,
            'notifications_max_toasts' => 3,
            'alerts_beacons_silent_enabled' => 1,
            'alerts_beacons_silent_cooldown_hours' => 4,
            'alerts_licenses_expiring_enabled' => 1,
            'alerts_licenses_expiring_warning_days' => 30,
            'alerts_licenses_expiring_cooldown_hours' => 24,
            'alerts_eez_declarations_enabled' => 1,
            'alerts_eez_declarations_position_window_hours' => 6,
            'alerts_eez_declarations_cooldown_hours' => 12,
            'eez_variance_tolerance_percent' => 10,
            'watch_shift_start_hour' => 8,
            'watch_shift_end_hour' => 18,
            'watch_teams_count' => 3,
            'watch_check_threshold' => 3,
        ], $overrides);
    }

    public function test_only_administrators_can_manage_settings(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->get(route('administration.settings'))->assertForbidden();
        $this->put(route('administration.settings.update'), $this->validPayload())->assertForbidden();
    }

    #[Covers('RG07', partial: true)]
    public function test_administrators_can_change_settings_and_they_apply_immediately(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('administration.settings'))->assertOk()->assertSee('notifications_poll_seconds');

        $this->put(route('administration.settings.update'), $this->validPayload(['notifications_poll_seconds' => 90, 'alerts_beacons_silent_enabled' => 0]))
            ->assertRedirect(route('administration.settings'));

        $this->assertSame(90, config('surveillance.notification_poll_seconds'));
        $this->assertFalse(config('surveillance.alerts.beacons_silent.enabled'));
        $this->assertSame(['notifications.poll_seconds', 'alerts.beacons_silent.enabled'], Setting::orderBy('id')->pluck('key')->all());
    }

    public function test_values_equal_to_the_default_are_not_stored(): void
    {
        $this->signInAs(Role::Admin);
        $this->put(route('administration.settings.update'), $this->validPayload(['notifications_poll_seconds' => 90]));

        $this->put(route('administration.settings.update'), $this->validPayload());

        $this->assertSame(0, Setting::count());
    }

    public function test_out_of_range_values_are_rejected(): void
    {
        $this->signInAs(Role::Admin);

        $this->put(route('administration.settings.update'), $this->validPayload(['notifications_poll_seconds' => 1]))
            ->assertSessionHasErrors('notifications_poll_seconds');

        $this->assertSame(0, Setting::count());
    }

    public function test_defaults_can_be_restored(): void
    {
        $this->signInAs(Role::Admin);
        $this->put(route('administration.settings.update'), $this->validPayload(['notifications_poll_seconds' => 90]));

        $this->delete(route('administration.settings.reset'))->assertRedirect(route('administration.settings'));

        $this->assertSame(30, config('surveillance.notification_poll_seconds'));
        $this->assertSame(0, Setting::count());
    }

    public function test_disabled_alerts_are_not_sent(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $this->signInAs(Role::Admin);
        $this->put(route('administration.settings.update'), $this->validPayload(['alerts_beacons_silent_enabled' => 0]));
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(10)]);

        $this->artisan('tracking:evaluate-alert-rules')->assertSuccessful();

        $this->assertSame(0, $agent->notifications()->count());
    }
}
