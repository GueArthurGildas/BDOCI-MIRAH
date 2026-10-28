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
        'admin@example.com' => [Role::Admin, 'Adjoua Konan'],
        'director@example.com' => [Role::Director, 'Mamadou Koné'],
        'teamlead@example.com' => [Role::TeamLead, 'Seydou Traoré'],
        'agent@example.com' => [Role::Agent, 'Aya Kouassi'],
    ];

    public function run(): void
    {
        $this->call([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        foreach (self::DEMO_USERS as $email => [$role, $name]) {
            User::factory()->create(['email' => $email, 'name' => $name])->assignRole($role->value);
        }

        $this->call([DemoDataSeeder::class, ConnectorActivitySeeder::class, Phase2DemoSeeder::class, Phase3aDemoSeeder::class, Phase4bDemoSeeder::class, Phase5DemoSeeder::class, Phase9DemoSeeder::class, Phase7DemoSeeder::class, Phase4aDemoSeeder::class, Phase3bDemoSeeder::class, Phase8DemoSeeder::class, Phase6DemoSeeder::class]);

        app(ModuleRegistry::class)->sync();
        app(VesselStateBuilder::class)->rebuildAll();
    }
}
