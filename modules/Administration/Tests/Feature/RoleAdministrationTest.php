<?php

namespace Modules\Administration\Tests\Feature;

use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

class RoleAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_manage_roles(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->get(route('administration.roles.index'))->assertForbidden();
        $this->post(route('administration.roles.store'), ['name' => 'Port officer'])->assertForbidden();
    }

    public function test_the_index_lists_system_and_custom_roles_with_the_permission_matrix(): void
    {
        $this->signInAs(Role::Admin);
        RoleModel::create(['name' => 'Port officer', 'guard_name' => 'web']);

        $this->get(route('administration.roles.index'))
            ->assertOk()
            ->assertSee('Port officer')
            ->assertSee(__('ui.roles.team_lead'))
            ->assertSee(__('administration::administration.permissions.vessels.view'));
    }

    public function test_a_custom_role_can_be_created_and_grants_exactly_its_permissions(): void
    {
        $this->signInAs(Role::Admin);

        $this->post(route('administration.roles.store'), [
            'name' => 'Port officer',
            'permissions' => [Permission::LicensesView->value, Permission::DashboardView->value],
        ])->assertRedirect(route('administration.roles.index'));

        $role = RoleModel::findByName('Port officer');
        $this->assertEqualsCanonicalizing(['licenses.view', 'dashboard.view'], $role->permissions->pluck('name')->all());

        $this->actingAs(User::factory()->create()->assignRole($role));
        $this->get('/licenses')->assertOk();
        $this->get('/vessels')->assertForbidden();
    }

    public function test_role_names_must_be_unique_and_permissions_valid(): void
    {
        $this->signInAs(Role::Admin);

        $this->post(route('administration.roles.store'), ['name' => Role::TeamLead->value])->assertSessionHasErrors('name');
        $this->post(route('administration.roles.store'), ['name' => 'Valid name', 'permissions' => ['nonsense.permission']])->assertSessionHasErrors('permissions.0');
        $this->assertSame(0, RoleModel::where('name', 'Valid name')->count());
    }

    public function test_permissions_and_name_of_a_custom_role_can_be_changed_and_are_audited(): void
    {
        $this->signInAs(Role::Admin);
        $role = RoleModel::create(['name' => 'Port officer', 'guard_name' => 'web'])->givePermissionTo(Permission::LicensesView->value);

        $this->put(route('administration.roles.update', $role), [
            'name' => 'Harbour officer',
            'permissions' => [Permission::VesselsView->value],
        ])->assertRedirect(route('administration.roles.index'));

        $role->refresh();
        $this->assertSame('Harbour officer', $role->name);
        $this->assertSame(['vessels.view'], $role->permissions->pluck('name')->all());

        $change = Activity::where('event', 'permissions_changed')->sole();
        $this->assertSame(['vessels.view'], $change->properties['added']);
        $this->assertSame(['licenses.view'], $change->properties['removed']);
    }

    public function test_permissions_of_a_system_role_are_editable_but_not_its_name(): void
    {
        $this->signInAs(Role::Admin);
        $inspector = RoleModel::findByName(Role::Agent->value);

        $this->put(route('administration.roles.update', $inspector), ['permissions' => [Permission::DashboardView->value]])
            ->assertRedirect();
        $this->assertSame(['dashboard.view'], $inspector->fresh()->permissions->pluck('name')->all());

        $this->put(route('administration.roles.update', $inspector), ['name' => 'Renamed', 'permissions' => []])
            ->assertSessionHasErrors('name');
        $this->assertSame(Role::Agent->value, $inspector->fresh()->name);
    }

    public function test_the_administrator_role_cannot_lose_permissions(): void
    {
        $this->signInAs(Role::Admin);
        $admin = RoleModel::findByName(Role::Admin->value);

        $this->put(route('administration.roles.update', $admin), ['permissions' => []])->assertRedirect();

        $this->assertCount(count(Role::Admin->permissions()), $admin->fresh()->permissions);
    }

    public function test_only_unused_custom_roles_can_be_deleted(): void
    {
        $this->signInAs(Role::Admin);
        $unused = RoleModel::create(['name' => 'Unused', 'guard_name' => 'web']);
        $used = RoleModel::create(['name' => 'Used', 'guard_name' => 'web']);
        User::factory()->create()->assignRole($used);

        $this->delete(route('administration.roles.destroy', $unused))->assertRedirect()->assertSessionHas('status');
        $this->assertNull(RoleModel::where('name', 'Unused')->first());

        $this->delete(route('administration.roles.destroy', $used))->assertSessionHas('error');
        $this->assertNotNull(RoleModel::where('name', 'Used')->first());

        $this->delete(route('administration.roles.destroy', RoleModel::findByName(Role::Agent->value)))->assertSessionHas('error');
        $this->assertNotNull(RoleModel::where('name', Role::Agent->value)->first());
    }

    public function test_custom_roles_can_be_assigned_to_users(): void
    {
        $this->signInAs(Role::Admin);
        RoleModel::create(['name' => 'Port officer', 'guard_name' => 'web']);

        $this->get(route('administration.users.create'))->assertOk()->assertSee('Port officer');

        $this->post(route('administration.users.store'), [
            'name' => 'Marie Kouadio',
            'email' => 'marie@example.com',
            'role' => 'Port officer',
            'is_active' => 1,
            'password' => 'a-strong-passphrase-42',
            'password_confirmation' => 'a-strong-passphrase-42',
        ])->assertRedirect(route('administration.users.index'));

        $this->assertTrue(User::where('email', 'marie@example.com')->first()->hasRole('Port officer'));
        $this->get(route('administration.users.index'))->assertSee('Port officer');
    }
}
