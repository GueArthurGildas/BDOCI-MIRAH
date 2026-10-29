<?php

namespace Modules\Administration\Tests\Feature;

use App\Models\RoleRecord;
use App\Models\User;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;
use App\Support\Authorization\PermissionResolver;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC24', partial: true)]
class RoleAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private function customRole(string $name = 'Port officer', array $permissions = [], ?RoleRecord $parent = null): RoleRecord
    {
        $role = RoleRecord::create(['name' => $name, 'parent_id' => $parent?->id]);
        app(GrantService::class)->syncRole($role, array_map(fn (Permission $permission) => $permission->value, $permissions));

        return $role;
    }

    private function direct(RoleRecord $role): array
    {
        return app(GrantService::class)->directCodesOfRole($role);
    }

    public function test_only_administrators_can_manage_roles(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->get(route('administration.roles.index'))->assertForbidden();
        $this->post(route('administration.roles.store'), ['name' => 'Port officer'])->assertForbidden();
    }

    public function test_the_index_lists_system_and_custom_roles_with_the_permission_matrix(): void
    {
        $this->signInAs(Role::Admin);
        $this->customRole();

        $this->get(route('administration.roles.index'))
            ->assertOk()
            ->assertSee('Port officer')
            ->assertSee(__('ui.roles.team_lead'))
            ->assertSee(__('administration::administration.permissions.vessels.view'));
    }

    public function test_deleting_a_role_asks_for_confirmation_in_the_themed_modal(): void
    {
        $this->signInAs(Role::Admin);
        $this->customRole('Port officer');

        $this->get(route('administration.roles.index'))
            ->assertSee('id="gs-confirm"', false)
            ->assertSee('data-confirm="'.e(__('administration::administration.roles.confirm_delete', ['role' => 'Port officer'])).'"', false)
            ->assertDontSee('onsubmit="return confirm(', false);
    }

    public function test_a_custom_role_can_be_created_and_grants_exactly_its_permissions(): void
    {
        $this->signInAs(Role::Admin);

        $this->post(route('administration.roles.store'), [
            'name' => 'Port officer',
            'description' => 'Follows port operations',
            'permissions' => [Permission::LicensesView->value, Permission::DashboardView->value],
        ])->assertRedirect(route('administration.roles.index'));

        $role = RoleRecord::where('name', 'Port officer')->firstOrFail();
        $this->assertSame('Follows port operations', $role->description);
        $this->assertSame(['dashboard.view', 'licenses.view'], $this->direct($role));

        $this->actingAs(User::factory()->create()->assignRole($role));
        $this->get('/licenses')->assertOk();
        $this->get('/vessels')->assertForbidden();
    }

    public function test_role_names_must_be_unique_and_permissions_valid(): void
    {
        $this->signInAs(Role::Admin);

        $this->post(route('administration.roles.store'), ['name' => Role::TeamLead->value])->assertSessionHasErrors('name');
        $this->post(route('administration.roles.store'), ['name' => 'Valid name', 'permissions' => ['nonsense.permission']])->assertSessionHasErrors('permissions.0');
        $this->assertSame(0, RoleRecord::where('name', 'Valid name')->count());
    }

    public function test_permissions_name_and_parent_of_a_custom_role_can_be_changed_and_are_audited(): void
    {
        $this->signInAs(Role::Admin);
        $role = $this->customRole(permissions: [Permission::LicensesView]);
        $agent = RoleRecord::where('name', Role::Agent->value)->firstOrFail();

        $this->put(route('administration.roles.update', $role), [
            'name' => 'Harbour officer',
            'parent_id' => $agent->id,
            'permissions' => [Permission::VesselsManage->value],
        ])->assertRedirect(route('administration.roles.index'));

        $role->refresh();
        $this->assertSame('Harbour officer', $role->name);
        $this->assertSame($agent->id, $role->parent_id);
        $this->assertSame(['vessels.manage'], $this->direct($role));

        $change = Activity::where('event', 'permissions_changed')->sole();
        $this->assertSame(['vessels.manage'], $change->properties['added']);
        $this->assertSame(['licenses.view'], $change->properties['removed']);
    }

    public function test_permissions_of_a_system_role_are_editable_but_not_its_name_or_parent(): void
    {
        $this->signInAs(Role::Admin);
        $agent = RoleRecord::where('name', Role::Agent->value)->firstOrFail();

        $this->put(route('administration.roles.update', $agent), ['permissions' => [Permission::DashboardView->value]])->assertRedirect();
        $this->assertSame(['dashboard.view'], $this->direct($agent->fresh()));

        $this->put(route('administration.roles.update', $agent), ['name' => 'Renamed', 'permissions' => []])->assertSessionHasErrors('name');
        $this->put(route('administration.roles.update', $agent), ['parent_id' => $agent->id, 'permissions' => []])->assertSessionHasErrors('parent_id');
        $this->assertSame(Role::Agent->value, $agent->fresh()->name);
    }

    public function test_the_administrator_role_cannot_lose_permissions(): void
    {
        $this->signInAs(Role::Admin);
        $admin = RoleRecord::where('name', Role::Admin->value)->firstOrFail();

        $this->put(route('administration.roles.update', $admin), ['permissions' => []])->assertRedirect();

        $this->assertCount(count(Role::Admin->permissions()), $this->direct($admin->fresh()));
    }

    public function test_only_unused_custom_roles_without_children_can_be_deleted(): void
    {
        $this->signInAs(Role::Admin);
        $unused = $this->customRole('Unused');
        $used = $this->customRole('Used');
        User::factory()->create()->assignRole($used);
        $parent = $this->customRole('Parent');
        $this->customRole('Child', parent: $parent);

        $this->delete(route('administration.roles.destroy', $unused))->assertRedirect()->assertSessionHas('status');
        $this->assertNull(RoleRecord::where('name', 'Unused')->first());

        $this->delete(route('administration.roles.destroy', $used))->assertSessionHas('error');
        $this->delete(route('administration.roles.destroy', $parent))->assertSessionHas('error');
        $this->delete(route('administration.roles.destroy', RoleRecord::where('name', Role::Agent->value)->firstOrFail()))->assertSessionHas('error');
        $this->assertSame(['Parent', 'Used'], RoleRecord::whereIn('name', ['Parent', 'Used'])->orderBy('name')->pluck('name')->all());
        $this->assertNotNull(RoleRecord::where('name', Role::Agent->value)->first());
    }

    public function test_custom_roles_can_be_assigned_to_users(): void
    {
        $this->signInAs(Role::Admin);
        $this->customRole();

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

    public function test_a_role_inherits_the_permissions_of_its_parent(): void
    {
        $this->signInAs(Role::Admin);
        $agent = RoleRecord::where('name', Role::Agent->value)->firstOrFail();
        $child = $this->customRole('Senior agent', [Permission::LicensesManage], parent: $agent);

        $this->actingAs(User::factory()->create()->assignRole($child));
        $this->get('/vessels')->assertOk();
        $this->get(route('licenses.create'))->assertOk();
        $this->get(route('administration.roles.index'))->assertForbidden();

        $this->signInAs(Role::Admin);
        $this->get(route('administration.roles.edit', $child))
            ->assertOk()
            ->assertSee('id="permission-vessels.view"', false)
            ->assertSee(__('administration::administration.roles.inherited'));
    }

    public function test_a_role_cannot_become_its_own_ancestor(): void
    {
        $this->signInAs(Role::Admin);
        $parent = $this->customRole('Parent');
        $child = $this->customRole('Child', parent: $parent);

        $this->put(route('administration.roles.update', $parent), ['name' => 'Parent', 'parent_id' => $child->id])->assertSessionHasErrors('parent_id');
        $this->put(route('administration.roles.update', $parent), ['name' => 'Parent', 'parent_id' => $parent->id])->assertSessionHasErrors('parent_id');
    }

    public function test_the_system_roles_form_the_chain_director_team_lead_agent(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $resolver = app(PermissionResolver::class);
        $role = fn (Role $role) => RoleRecord::where('name', $role->value)->firstOrFail();

        $this->assertSame($role(Role::TeamLead)->id, $role(Role::Director)->parent_id);
        $this->assertSame($role(Role::Agent)->id, $role(Role::TeamLead)->parent_id);
        $this->assertNull($role(Role::Agent)->parent_id);

        $agent = $resolver->actionsForRole($role(Role::Agent));
        $teamLead = $resolver->actionsForRole($role(Role::TeamLead));
        $this->assertSame([], array_diff($agent, $teamLead));
        $this->assertEqualsCanonicalizing([...$teamLead, Permission::InfractionsDecideMeasures->value, Permission::MissionsAuthorizeAsDirector->value], $resolver->actionsForRole($role(Role::Director)));
        $this->assertEqualsCanonicalizing(array_map(fn (Permission $permission) => $permission->value, Role::Agent->permissions()), $agent);
        $this->assertEqualsCanonicalizing(array_map(fn (Permission $permission) => $permission->value, Role::TeamLead->permissions()), $teamLead);
    }
}
