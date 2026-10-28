<?php

namespace Tests\Feature\Kernel;

use App\Models\ActionCatalogEntry;
use App\Models\PermissionGrant;
use App\Models\PermissionGroup;
use App\Models\RoleRecord;
use App\Models\User;
use App\Models\Zone;
use App\Support\Authorization\ActionCatalog;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;
use App\Support\Authorization\PermissionResolver;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC24', partial: true)]
class PermissionResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function roleWith(array $permissions, ?RoleRecord $parent = null): RoleRecord
    {
        $role = RoleRecord::create(['name' => 'r-'.fake()->unique()->word(), 'parent_id' => $parent?->id]);
        app(GrantService::class)->syncRole($role, array_map(fn (Permission $permission) => $permission->value, $permissions));

        return $role;
    }

    private function grant(array $attributes): PermissionGrant
    {
        app(PermissionResolver::class)->flush();

        return PermissionGrant::create($attributes + ['action_id' => app(ActionCatalog::class)->idFor(Permission::LicensesManage)]);
    }

    public function test_a_user_holds_the_permissions_of_the_roles_and_of_their_ancestors(): void
    {
        $grandParent = $this->roleWith([Permission::DashboardView]);
        $parent = $this->roleWith([Permission::VesselsView], $grandParent);
        $child = $this->roleWith([Permission::LicensesView], $parent);
        $user = User::factory()->create()->assignRole($child);

        $this->assertEqualsCanonicalizing(['dashboard.view', 'vessels.view', 'licenses.view'], app(PermissionResolver::class)->actionsFor($user));
        $this->assertTrue($user->can('vessels.view'));
        $this->assertFalse($user->can('vessels.manage'));
    }

    public function test_group_membership_grants_permissions_on_top_of_the_role(): void
    {
        $agent = User::factory()->create()->assignRole(Role::Agent->value);
        $this->assertFalse($agent->can(Permission::LicensesManage->value));

        $agent->permissionGroups()->attach(PermissionGroup::where('code', 'fnp_license_management')->sole());

        $this->assertTrue($agent->can(Permission::LicensesManage->value));
        $this->assertFalse($agent->can(Permission::InfractionsManage->value));
    }

    public function test_grants_only_apply_inside_their_validity_period(): void
    {
        $role = $this->roleWith([]);
        $user = User::factory()->create()->assignRole($role);
        $code = Permission::LicensesManage->value;

        $future = $this->grant(['role_id' => $role->id, 'valid_from' => today()->addDay()]);
        $this->assertFalse($user->can($code));
        $future->delete();

        $past = $this->grant(['role_id' => $role->id, 'valid_to' => today()->subDay()]);
        $this->assertFalse($user->can($code));
        $past->delete();

        $this->grant(['role_id' => $role->id, 'valid_from' => today()->subDay(), 'valid_to' => today()->addDay()]);
        $this->assertTrue($user->can($code));
    }

    public function test_a_zone_scoped_grant_only_applies_in_that_zone(): void
    {
        $role = $this->roleWith([]);
        $user = User::factory()->create()->assignRole($role);
        [$abidjan, $sanPedro] = [Zone::factory()->create(), Zone::factory()->create()];
        $code = Permission::LicensesManage->value;

        $this->grant(['role_id' => $role->id, 'zone_id' => $abidjan->id]);

        $this->assertFalse($user->can($code));
        $this->assertTrue(Gate::forUser($user)->allows($code, $abidjan));
        $this->assertFalse(Gate::forUser($user)->allows($code, $sanPedro));

        app(GrantService::class)->syncRole($role, [$code]);
        $this->assertTrue(Gate::forUser($user)->allows($code, $sanPedro));
    }

    public function test_holders_include_descendant_roles_and_group_members_but_not_inactive_or_scoped_grants(): void
    {
        $parent = $this->roleWith([Permission::LicensesManage]);
        $child = $this->roleWith([], $parent);
        $viaRole = User::factory()->create()->assignRole($parent);
        $viaChild = User::factory()->create()->assignRole($child);
        $viaGroup = User::factory()->create();
        $viaGroup->permissionGroups()->attach(PermissionGroup::where('code', 'fnp_license_management')->sole());
        $inactive = User::factory()->create(['is_active' => false])->assignRole($parent);
        $unrelated = User::factory()->create()->assignRole(Role::Agent->value);
        $scopedRole = $this->roleWith([]);
        $this->grant(['role_id' => $scopedRole->id, 'zone_id' => Zone::factory()->create()->id]);
        $scoped = User::factory()->create()->assignRole($scopedRole);

        $holders = app(PermissionResolver::class)->holders(Permission::LicensesManage->value)->pluck('id');

        $this->assertEqualsCanonicalizing([$viaRole->id, $viaChild->id, $viaGroup->id], $holders->all());
        $this->assertNotContains($inactive->id, $holders->all());
        $this->assertNotContains($unrelated->id, $holders->all());
        $this->assertNotContains($scoped->id, $holders->all());
    }

    public function test_granting_through_the_service_takes_effect_immediately_and_reports_the_difference(): void
    {
        $role = $this->roleWith([Permission::VesselsView]);
        $user = User::factory()->create()->assignRole($role);
        $this->assertTrue($user->can('vessels.view'));

        $changes = app(GrantService::class)->syncRole($role, [Permission::LicensesView->value]);

        $this->assertSame(['added' => ['licenses.view'], 'removed' => ['vessels.view']], $changes);
        $this->assertFalse($user->can('vessels.view'));
        $this->assertTrue($user->can('licenses.view'));
    }

    public function test_the_catalog_lists_every_permission_once_with_its_module_and_verb(): void
    {
        app(ActionCatalog::class)->sync();
        app(ActionCatalog::class)->sync();

        $this->assertSame(count(Permission::cases()), ActionCatalogEntry::count());

        $entry = ActionCatalogEntry::with('module')->where('code', 'licenses.manage')->sole();
        $this->assertSame('licenses', $entry->module->code);
        $this->assertSame('update', $entry->verb);
        $this->assertSame('administration', ActionCatalogEntry::with('module')->where('code', 'roles.manage')->sole()->module->code);
        $this->assertSame('view', ActionCatalogEntry::where('code', 'vessels.view')->sole()->verb);
    }

    public function test_the_database_refuses_a_grant_without_exactly_one_holder_and_duplicate_permanent_grants(): void
    {
        $role = $this->roleWith([Permission::VesselsView]);
        $group = PermissionGroup::first();
        $actionId = app(ActionCatalog::class)->idFor(Permission::VesselsView);

        $this->assertRejected(fn () => PermissionGrant::create(['action_id' => $actionId]));
        $this->assertRejected(fn () => PermissionGrant::create(['action_id' => $actionId, 'role_id' => $role->id, 'permission_group_id' => $group->id]));
        $this->assertRejected(fn () => PermissionGrant::create(['action_id' => $actionId, 'role_id' => $role->id]));
    }

    private function assertRejected(callable $write): void
    {
        try {
            DB::transaction($write);
            $this->fail('The database accepted an invalid grant.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }

    public function test_the_sync_command_adds_new_permissions_to_the_catalog(): void
    {
        ActionCatalogEntry::where('code', 'vessels.view')->delete();

        $this->artisan('permissions:sync')->expectsOutputToContain('actions in the catalog')->assertSuccessful();

        $this->assertSame(1, ActionCatalogEntry::where('code', 'vessels.view')->count());
    }
}
