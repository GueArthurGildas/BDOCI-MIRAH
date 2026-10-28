<?php

namespace Modules\Missions\Tests\Feature;

use App\Models\Agent;
use App\Models\Asset;
use App\Models\ExternalInspection;
use App\Models\Mission;
use App\Models\MissionAsset;
use App\Models\MissionParticipant;
use App\Models\ObservedUnit;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class MissionPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_permission_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/missions')->assertForbidden();
    }

    #[Covers('UC18', partial: true)]
    public function test_mission_detail_shows_team_and_assets(): void
    {
        $this->signInAs(Role::Agent);
        $mission = Mission::factory()->create(['code' => 'MIS-2026-0042', 'objectives' => 'Contrôle des chalutiers']);
        MissionParticipant::factory()->create(['mission_id' => $mission->id, 'agent_id' => Agent::factory()->create(['name' => 'Awa Koné'])->id]);
        MissionAsset::factory()->create(['mission_id' => $mission->id, 'asset_id' => Asset::factory()->create(['label' => 'Vedette CSP 03'])->id]);

        $this->get('/missions')->assertOk()->assertSee('MIS-2026-0042');
        $this->get(route('missions.show', $mission))->assertOk()->assertSee('Awa Koné')->assertSee('Vedette CSP 03');
    }

    #[Covers('UC18', partial: true)]
    public function test_missions_can_be_filtered_by_status_and_type(): void
    {
        $this->signInAs(Role::Agent);
        Mission::factory()->create(['code' => 'MIS-A', 'type' => 'maritime']);
        Mission::factory()->create(['code' => 'MIS-B', 'type' => 'port', 'status' => 'closed']);

        $this->get('/missions?type=port')->assertOk()->assertSee('MIS-B')->assertDontSee('MIS-A');
        $this->get('/missions?status=scheduled')->assertOk()->assertSee('MIS-A')->assertDontSee('MIS-B');
    }

    #[Covers('UC18', partial: true)]
    public function test_vessels_often_seen_but_never_inspected_are_listed(): void
    {
        $this->signInAs(Role::Agent);
        $mission = Mission::factory()->create();
        $seen = Vessel::factory()->create(['name' => 'JAMAIS CONTRÔLÉ']);
        $inspected = Vessel::factory()->create(['name' => 'DÉJÀ CONTRÔLÉ']);
        ObservedUnit::factory()->create(['mission_id' => $mission->id, 'vessel_id' => $seen->id, 'level' => 'sighted']);
        ObservedUnit::factory()->create(['mission_id' => $mission->id, 'vessel_id' => $inspected->id, 'level' => 'sighted']);
        ObservedUnit::factory()->create(['mission_id' => $mission->id, 'vessel_id' => $inspected->id, 'level' => 'inspected', 'inspection_id' => ExternalInspection::factory()->create()->id]);

        $this->get('/missions')->assertOk()->assertSee('JAMAIS CONTRÔLÉ')->assertDontSee('DÉJÀ CONTRÔLÉ');
    }
}
