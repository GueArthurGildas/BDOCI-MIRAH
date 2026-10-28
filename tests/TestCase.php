<?php

namespace Tests;

use App\Models\RoleRecord;
use App\Models\User;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function signInWithPermissions(Permission ...$permissions): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $role = RoleRecord::create(['name' => 'test-role-'.Str::random(8)]);
        app(GrantService::class)->syncRole($role, array_map(fn (Permission $permission) => $permission->value, $permissions));

        $user = User::factory()->create()->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    protected function signInAs(Role $role, array $attributes = []): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create($attributes)->assignRole($role->value);
        $this->actingAs($user);

        return $user;
    }
}
