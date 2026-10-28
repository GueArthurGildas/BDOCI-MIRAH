<?php

namespace Modules\Tracking\Tests\Feature;

use App\Models\Position;
use App\Models\TrackingDevice;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class SilentBeaconsNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC06', partial: true)]
    public function test_tracking_viewers_are_notified_once_per_cooldown_when_beacons_are_silent(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $admin = $this->signInAs(Role::Admin);
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(10)]);

        $this->artisan('tracking:evaluate-alert-rules')->assertSuccessful();
        $this->artisan('tracking:evaluate-alert-rules')->assertSuccessful();

        $this->assertSame(1, $agent->notifications()->count());
        $this->assertSame(0, $admin->notifications()->count());
        $this->assertSame(1, $agent->notifications()->first()->data['params']['count']);
    }

    public function test_nobody_is_notified_when_every_beacon_emits(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subMinutes(10)]);

        $this->artisan('tracking:evaluate-alert-rules')->assertSuccessful();

        $this->assertSame(0, $agent->notifications()->count());
    }

    public function test_a_new_notification_is_sent_after_the_cooldown(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(10)]);

        $this->artisan('tracking:evaluate-alert-rules');
        $this->travel(5)->hours();
        $this->artisan('tracking:evaluate-alert-rules');

        $this->assertSame(2, $agent->notifications()->count());
    }
}
