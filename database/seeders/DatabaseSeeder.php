<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Events\VesselStateBuilder;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const DEMO_USERS = [
        'admin@example.com' => Role::Admin,
        'director@example.com' => Role::Director,
        'teamlead@example.com' => Role::TeamLead,
        'agent@example.com' => Role::Agent,
    ];

    public function run(): void
    {
        $this->call([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        foreach (self::DEMO_USERS as $email => $role) {
            User::factory()->create(['email' => $email])->assignRole($role->value);
        }

        $this->call([DemoDataSeeder::class, ConnectorActivitySeeder::class, Phase2DemoSeeder::class, Phase3aDemoSeeder::class, Phase4bDemoSeeder::class, Phase5DemoSeeder::class, Phase9DemoSeeder::class, Phase7DemoSeeder::class, Phase4aDemoSeeder::class]);

        app(ModuleRegistry::class)->sync();
        app(VesselStateBuilder::class)->rebuildAll();
    }
}
