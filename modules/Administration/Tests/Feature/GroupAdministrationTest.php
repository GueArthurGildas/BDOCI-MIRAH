<?php

namespace Modules\Administration\Tests\Feature;

use App\Models\PermissionGroup;
use App\Models\User;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC24', partial: true)]
class GroupAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private function customGroup(string $label = 'Port desk', array $permissions = [], array $members = []): PermissionGroup
    {
        $group = PermissionGroup::create(['code' => str($label)->slug('_')->toString(), 'label' => $label]);
        app(GrantService::class)->syncGroup($group, array_map(fn (Permission $permission) => $permission->value, $permissions));
        $group->members()->sync(collect($members)->map->id->all());

        return $group;
    }

    public function test_only_administrators_manage_groups(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->get(route('administration.groups.index'))->assertForbidden();
        $this->post(route('administration.groups.store'), ['label' => 'Port desk'])->assertForbidden();
    }

    public function test_the_five_system_groups_of_the_specification_exist_with_translated_labels(): void
    {
        $this->signInAs(Role::Admin);

        $this->assertEqualsCanonicalizing(
            ['fnp_license_management', 'infraction_investigation', 'surveillance_opinion', 'automatic_dispatch', 'trigger_ai_agent'],
            PermissionGroup::where('is_system', true)->pluck('code')->all(),
        );

        $this->get(route('administration.groups.index'))
            ->assertOk()
            ->assertSee(__('administration::groups.system.fnp_license_management.label'))
            ->assertSee(__('administration::groups.system.trigger_ai_agent.label'));
    }

    public function test_a_custom_group_grants_its_permissions_to_its_members_and_is_audited(): void
    {
        $admin = $this->signInAs(Role::Admin);
        $agent = User::factory()->create(['name' => 'Aya Agent'])->assignRole(Role::Agent->value);
        $this->assertFalse($agent->can(Permission::LicensesManage->value));

        $this->post(route('administration.groups.store'), [
            'label' => 'Port desk',
            'permissions' => [Permission::LicensesManage->value],
            'members' => [$agent->id],
        ])->assertRedirect(route('administration.groups.index'));

        $group = PermissionGroup::where('label', 'Port desk')->sole();
        $this->assertSame('port_desk', $group->code);
        $this->assertTrue($agent->can(Permission::LicensesManage->value));

        $this->actingAs($agent);
        $this->get(route('licenses.create'))->assertOk();

        $this->assertSame(['Aya Agent'], Activity::where('event', 'members_changed')->sole()->properties['added']);
        $this->assertSame(['licenses.manage'], Activity::where('event', 'permissions_changed')->sole()->properties['added']);
        $this->assertSame($admin->id, Activity::where('event', 'created')->where('subject_type', $group->getMorphClass())->sole()->causer_id);
    }

    public function test_removing_a_member_or_a_permission_takes_effect_immediately(): void
    {
        $admin = $this->signInAs(Role::Admin);
        $agent = User::factory()->create()->assignRole(Role::Agent->value);
        $group = $this->customGroup(permissions: [Permission::LicensesManage], members: [$agent]);
        $this->assertTrue($agent->can(Permission::LicensesManage->value));

        $this->actingAs($admin)->put(route('administration.groups.update', $group), ['label' => 'Port desk', 'permissions' => [Permission::LicensesManage->value], 'members' => []]);
        $this->assertFalse($agent->fresh()->can(Permission::LicensesManage->value));

        $this->put(route('administration.groups.update', $group), ['label' => 'Port desk', 'permissions' => [Permission::LicensesManage->value], 'members' => [$agent->id]]);
        $this->assertTrue($agent->fresh()->can(Permission::LicensesManage->value));

        $this->put(route('administration.groups.update', $group), ['label' => 'Port desk', 'permissions' => [], 'members' => [$agent->id]]);
        $this->assertFalse($agent->fresh()->can(Permission::LicensesManage->value));
    }

    public function test_system_groups_keep_their_name_and_cannot_be_deleted(): void
    {
        $this->signInAs(Role::Admin);
        $group = PermissionGroup::where('code', 'infraction_investigation')->sole();

        $this->put(route('administration.groups.update', $group), ['label' => 'Renamed', 'permissions' => []])->assertSessionHasErrors('label');
        $this->put(route('administration.groups.update', $group), ['permissions' => [Permission::InfractionsView->value]])->assertRedirect();
        $this->assertSame(['infractions.view'], app(GrantService::class)->directCodesOfGroup($group));

        $this->delete(route('administration.groups.destroy', $group))->assertSessionHas('error');
        $this->assertNotNull($group->fresh());
    }

    public function test_deleting_a_custom_group_removes_its_memberships_and_the_permissions_they_gave(): void
    {
        $admin = $this->signInAs(Role::Admin);
        $agent = User::factory()->create()->assignRole(Role::Agent->value);
        $group = $this->customGroup(permissions: [Permission::LicensesManage], members: [$agent]);
        $this->assertTrue($agent->can(Permission::LicensesManage->value));

        $this->actingAs($admin)->delete(route('administration.groups.destroy', $group))->assertSessionHas('status');

        $this->assertNull(PermissionGroup::find($group->id));
        $this->assertFalse($agent->fresh()->can(Permission::LicensesManage->value));
        $this->assertSame(0, DB::table('permission_group_user')->count());
    }

    public function test_groups_can_be_assigned_from_the_user_form_and_the_change_is_audited(): void
    {
        $this->signInAs(Role::Admin);
        $group = PermissionGroup::where('code', 'fnp_license_management')->sole();

        $this->post(route('administration.users.store'), [
            'name' => 'Marie Kouadio',
            'email' => 'marie@example.com',
            'role' => Role::Agent->value,
            'groups' => [$group->id],
            'is_active' => 1,
            'password' => 'a-strong-passphrase-42',
            'password_confirmation' => 'a-strong-passphrase-42',
        ])->assertRedirect(route('administration.users.index'));

        $user = User::where('email', 'marie@example.com')->sole();
        $this->assertTrue($user->can(Permission::LicensesManage->value));
        $this->assertSame(['fnp_license_management'], Activity::where('event', 'groups_changed')->sole()->properties['added']);

        $this->get(route('administration.users.edit', $user))->assertOk()->assertSee(__('administration::groups.system.fnp_license_management.label'));
        $this->put(route('administration.users.update', $user), ['name' => 'Marie Kouadio', 'email' => 'marie@example.com', 'role' => Role::Agent->value, 'is_active' => 1]);
        $this->assertFalse($user->fresh()->can(Permission::LicensesManage->value));
    }
}
