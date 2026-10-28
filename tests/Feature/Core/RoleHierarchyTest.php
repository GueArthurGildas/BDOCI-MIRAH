<?php

namespace Tests\Feature\Core;

use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_inherits_the_permissions_of_the_role_below(): void
    {
        $agent = collect(Role::SurveillanceAgent->permissions())->map->value;
        $lead = collect(Role::TeamLead->permissions())->map->value;
        $director = collect(Role::Director->permissions())->map->value;

        $this->assertEmpty($agent->diff($lead));
        $this->assertEmpty($lead->diff($director));
        $this->assertSame($lead->unique()->count(), $lead->count());
    }

    public function test_admin_has_technical_rights_only(): void
    {
        $admin = collect(Role::Admin->permissions());

        $this->assertTrue($admin->contains(Permission::UsersManage));
        $this->assertTrue($admin->contains(Permission::SettingsManage));
        $this->assertFalse($admin->contains(Permission::VesselsManage));
        $this->assertFalse($admin->contains(Permission::InfractionsManage));
        $this->assertFalse($admin->contains(Permission::LicensesManage));
    }

    public function test_home_redirects_admin_to_an_allowed_page(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('home'))->assertRedirect();
        $this->followingRedirects()->get(route('home'))->assertOk();
    }
}
