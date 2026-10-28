<?php

namespace Modules\Licenses\Tests\Feature;

use App\Models\License;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('RG07', 'UC03', partial: true)]
class ExpiringLicensesNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_viewers_are_notified_with_the_number_of_expiring_licenses(): void
    {
        $inspector = $this->signInAs(Role::Agent);
        License::factory()->expiringIn(10)->count(2)->create();
        License::factory()->expiringIn(120)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $this->assertSame(1, $inspector->notifications()->count());
        $this->assertSame(2, $inspector->notifications()->first()->data['params']['count']);
    }

    public function test_nobody_is_notified_without_expiring_licenses(): void
    {
        $inspector = $this->signInAs(Role::Agent);
        License::factory()->expiringIn(120)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $this->assertSame(0, $inspector->notifications()->count());
    }

    public function test_deactivated_users_are_not_notified(): void
    {
        $inspector = $this->signInAs(Role::Agent, ['is_active' => false]);
        License::factory()->expiringIn(10)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $this->assertSame(0, $inspector->notifications()->count());
    }

    public function test_members_of_a_group_granting_the_permission_are_notified_too(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $member = User::factory()->create();
        $member->permissionGroups()->attach(PermissionGroup::where('code', 'fnp_license_management')->sole());
        License::factory()->expiringIn(10)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $this->assertSame(1, $member->notifications()->count());
    }
}
