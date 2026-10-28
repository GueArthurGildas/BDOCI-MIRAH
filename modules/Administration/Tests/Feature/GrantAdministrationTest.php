<?php

namespace Modules\Administration\Tests\Feature;

use App\Models\PermissionGrant;
use App\Models\PermissionGroup;
use App\Models\RoleRecord;
use App\Models\User;
use App\Models\Zone;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC24', partial: true)]
class GrantAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private function agentWithRole(RoleRecord $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_only_administrators_manage_limited_permissions(): void
    {
        $this->signInAs(Role::TeamLead);
        $role = RoleRecord::where('name', Role::Agent->value)->firstOrFail();

        $this->post(route('administration.roles.grants.store', $role), ['action' => Permission::LicensesManage->value, 'zone_id' => Zone::factory()->create()->id])->assertForbidden();
        $this->assertSame(0, PermissionGrant::whereNotNull('zone_id')->count());
    }

    public function test_a_zone_limited_permission_applies_only_to_that_zone(): void
    {
        $this->signInAs(Role::Admin);
        $role = RoleRecord::create(['name' => 'Zone officer']);
        [$west, $east] = [Zone::factory()->create(), Zone::factory()->create()];

        $this->post(route('administration.roles.grants.store', $role), ['action' => Permission::LicensesManage->value, 'zone_id' => $west->id])
            ->assertRedirect(route('administration.roles.edit', $role));

        $this->actingAs($this->agentWithRole($role));
        $this->assertTrue(Gate::allows(Permission::LicensesManage->value, $west));
        $this->assertFalse(Gate::allows(Permission::LicensesManage->value, $east));
        $this->assertFalse(Gate::allows(Permission::LicensesManage->value));

        $this->assertSame(1, Activity::where('event', 'grant_added')->count());
    }

    public function test_a_period_limited_permission_expires(): void
    {
        $this->signInAs(Role::Admin);
        $group = PermissionGroup::create(['code' => 'temp', 'label' => 'Temporary desk']);
        $holder = User::factory()->create();
        $group->members()->sync([$holder->id]);

        $this->post(route('administration.groups.grants.store', $group), ['action' => Permission::LicensesManage->value, 'valid_from' => today()->subDays(10)->toDateString(), 'valid_to' => today()->subDay()->toDateString()])
            ->assertRedirect(route('administration.groups.edit', $group));
        $this->assertFalse($holder->can(Permission::LicensesManage->value));

        $this->post(route('administration.groups.grants.store', $group), ['action' => Permission::LicensesManage->value, 'valid_from' => today()->toDateString(), 'valid_to' => today()->addWeek()->toDateString()]);
        $this->assertTrue($holder->fresh()->can(Permission::LicensesManage->value));
    }

    public function test_removing_a_limited_permission_takes_effect_and_is_audited(): void
    {
        $this->signInAs(Role::Admin);
        $group = PermissionGroup::create(['code' => 'temp', 'label' => 'Temporary desk']);
        $holder = User::factory()->create();
        $group->members()->sync([$holder->id]);
        $this->post(route('administration.groups.grants.store', $group), ['action' => Permission::LicensesManage->value, 'valid_to' => today()->addWeek()->toDateString()]);
        $this->assertTrue($holder->fresh()->can(Permission::LicensesManage->value));

        $this->delete(route('administration.grants.destroy', PermissionGrant::where('permission_group_id', $group->id)->sole()))->assertRedirect();

        $this->assertFalse($holder->fresh()->can(Permission::LicensesManage->value));
        $this->assertSame(1, Activity::where('event', 'grant_removed')->count());
    }

    public function test_a_limited_permission_needs_a_zone_or_a_period_and_a_coherent_range(): void
    {
        $this->signInAs(Role::Admin);
        $role = RoleRecord::where('name', Role::Agent->value)->firstOrFail();

        $this->post(route('administration.roles.grants.store', $role), ['action' => Permission::LicensesManage->value])->assertSessionHasErrors('zone_id');
        $this->post(route('administration.roles.grants.store', $role), ['action' => 'nonsense', 'valid_from' => today()->toDateString()])->assertSessionHasErrors('action');
        $this->post(route('administration.roles.grants.store', $role), ['action' => Permission::LicensesManage->value, 'valid_from' => today()->toDateString(), 'valid_to' => today()->subDay()->toDateString()])->assertSessionHasErrors('valid_to');

        $this->assertSame(0, PermissionGrant::where('role_id', $role->id)->where(fn ($query) => $query->whereNotNull('zone_id')->orWhereNotNull('valid_from')->orWhereNotNull('valid_to'))->count());
    }

    public function test_the_administrator_role_cannot_receive_business_grants(): void
    {
        $this->signInAs(Role::Admin);
        $admin = RoleRecord::where('name', Role::Admin->value)->firstOrFail();

        $this->post(route('administration.roles.grants.store', $admin), ['action' => Permission::LicensesManage->value, 'valid_from' => today()->toDateString()])->assertForbidden();
    }

    public function test_the_edit_pages_list_the_limited_permissions(): void
    {
        $this->signInAs(Role::Admin);
        $role = RoleRecord::create(['name' => 'Zone officer']);
        $zone = Zone::factory()->create(['name' => 'ZEE OUEST']);
        $this->post(route('administration.roles.grants.store', $role), ['action' => Permission::LicensesManage->value, 'zone_id' => $zone->id]);

        $this->get(route('administration.roles.edit', $role))->assertOk()->assertSee('ZEE OUEST')->assertSee(route('administration.roles.grants.store', $role));
        $this->get(route('administration.groups.edit', PermissionGroup::first()))->assertOk()->assertSee(__('administration::grants.title'));
    }
}
