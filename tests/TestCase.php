<?php

namespace Tests;

use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function signInAs(Role $role, array $attributes = []): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create($attributes)->assignRole($role->value);
        $this->actingAs($user);

        return $user;
    }
}
