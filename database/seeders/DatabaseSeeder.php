<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Authorization\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const DEMO_USERS = [
        'admin@example.com' => Role::Admin,
        'supervisor@example.com' => Role::Supervisor,
        'agent@example.com' => Role::SurveillanceAgent,
        'inspector@example.com' => Role::Inspector,
    ];

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        foreach (self::DEMO_USERS as $email => $role) {
            User::factory()->create(['email' => $email])->assignRole($role->value);
        }
    }
}
