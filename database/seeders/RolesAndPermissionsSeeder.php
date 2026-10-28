<?php

namespace Database\Seeders;

use App\Support\Authorization\Permission;
use App\Support\Authorization\PermissionGroup;
use App\Support\Authorization\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Role::cases() as $role) {
            RoleModel::findOrCreate($role->value)->syncPermissions($this->names($role->permissions()));
        }

        foreach (PermissionGroup::cases() as $group) {
            RoleModel::findOrCreate($group->roleName())->syncPermissions($this->names($group->permissions()));
        }
    }

    /** @param array<int, Permission> $permissions */
    private function names(array $permissions): array
    {
        return array_map(fn (Permission $permission) => $permission->value, $permissions);
    }
}
