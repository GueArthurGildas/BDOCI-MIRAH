<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use App\Models\RoleRecord;
use App\Support\Authorization\ActionCatalog;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    private const SYSTEM_GROUPS = [
        'fnp_license_management' => [Permission::LicensesView, Permission::LicensesManage],
        'infraction_investigation' => [Permission::InfractionsView, Permission::InfractionsManage],
        'surveillance_opinion' => [],
        'automatic_dispatch' => [Permission::CommunicationsManageDispatch],
        'trigger_ai_agent' => [Permission::CommunicationsTriggerAiAgent],
    ];

    public function run(): void
    {
        app(ActionCatalog::class)->sync();
        $grants = app(GrantService::class);

        $records = collect(Role::cases())->mapWithKeys(fn (Role $role) => [$role->value => RoleRecord::firstOrCreate(['name' => $role->value])]);

        foreach (Role::cases() as $role) {
            $record = $records[$role->value];
            $record->update(['parent_id' => $role->parent() ? $records[$role->parent()->value]->id : null]);

            if ($record->wasRecentlyCreated || $role === Role::Admin) {
                $grants->syncRole($record, $this->ownActions($role));
            }
        }

        foreach (self::SYSTEM_GROUPS as $code => $permissions) {
            $group = PermissionGroup::firstOrCreate(['code' => $code], ['is_system' => true]);

            if ($group->wasRecentlyCreated) {
                $grants->syncGroup($group, array_map(fn (Permission $permission) => $permission->value, $permissions));
            }
        }
    }

    /** @return array<int, string> */
    private function ownActions(Role $role): array
    {
        $inherited = $role->parent()?->permissions() ?? [];

        return collect($role->permissions())
            ->reject(fn (Permission $permission) => in_array($permission, $inherited, true))
            ->map(fn (Permission $permission) => $permission->value)
            ->values()
            ->all();
    }
}
