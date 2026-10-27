<?php

namespace Modules\Licenses\Tests\Feature;

use App\Models\License;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiringLicensesNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_viewers_are_notified_with_the_number_of_expiring_licenses(): void
    {
        $inspector = $this->signInAs(Role::Inspector);
        License::factory()->expiringIn(10)->count(2)->create();
        License::factory()->expiringIn(120)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $this->assertSame(1, $inspector->notifications()->count());
        $this->assertSame(2, $inspector->notifications()->first()->data['params']['count']);
    }

    public function test_nobody_is_notified_without_expiring_licenses(): void
    {
        $inspector = $this->signInAs(Role::Inspector);
        License::factory()->expiringIn(120)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $this->assertSame(0, $inspector->notifications()->count());
    }

    public function test_deactivated_users_are_not_notified(): void
    {
        $inspector = $this->signInAs(Role::Inspector, ['is_active' => false]);
        License::factory()->expiringIn(10)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $this->assertSame(0, $inspector->notifications()->count());
    }
}
