<?php

namespace Database\Seeders;

use App\Support\Authorization\Permission;
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
            $model = RoleModel::findOrCreate($role->value);

            if ($model->wasRecentlyCreated || $role === Role::Admin) {
                $model->syncPermissions(array_map(fn (Permission $permission) => $permission->value, $role->permissions()));
            }
        }
    }
}
