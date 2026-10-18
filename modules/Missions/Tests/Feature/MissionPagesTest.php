<?php

namespace Modules\Missions\Tests\Feature;

use App\Models\Mission;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_permission_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/missions')->assertForbidden();
    }

    public function test_mission_detail_shows_vessels_and_agents(): void
    {
        $this->signInAs(Role::Inspector);
        $mission = Mission::factory()->create(['reference' => 'MIS-2026-042']);
        $mission->vessels()->attach(Vessel::factory()->patrol()->create(['name' => 'VEDETTE CSP 03']));
        $mission->agents()->attach(User::factory()->create(['name' => 'Awa Koné']));

        $this->get('/missions')->assertOk()->assertSee('MIS-2026-042');
        $this->get(route('missions.show', $mission))->assertOk()->assertSee('VEDETTE CSP 03')->assertSee('Awa Koné');
    }
}
