<?php

namespace Modules\Logbook\Tests\Feature;

use App\Enums\ObservationLevel;
use App\Enums\ObservationStatus;
use App\Models\Observation;
use App\Models\Team;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogbookPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrators_cannot_access_the_logbook(): void
    {
        $this->signInAs(Role::Admin);

        $this->get('/logbook')->assertForbidden();
    }

    public function test_pending_filter_only_lists_items_to_hand_over(): void
    {
        $this->signInAs(Role::Agent);
        Observation::factory()->create(['description' => 'Navire suspect au large', 'status' => ObservationStatus::Open]);
        Observation::factory()->create(['description' => 'Situation régularisée', 'status' => ObservationStatus::Closed]);

        $this->get('/logbook?pending=1')
            ->assertOk()
            ->assertSee('Navire suspect au large')
            ->assertDontSee('Situation régularisée');
    }

    public function test_agents_record_observations_with_their_team(): void
    {
        $team = Team::factory()->create();
        $agent = $this->signInAs(Role::Agent, ['team_id' => $team->id]);
        $vessel = Vessel::factory()->create();

        $this->post('/logbook', [
            'observed_at' => now()->subHour()->format('Y-m-d H:i'),
            'vessel_id' => $vessel->id,
            'level' => ObservationLevel::Warning->value,
            'description' => 'Rupture d\'émission constatée.',
        ])->assertRedirect();

        $observation = Observation::sole();
        $this->assertSame($agent->id, $observation->author_id);
        $this->assertSame($team->id, $observation->team_id);
        $this->assertSame(ObservationStatus::Open, $observation->status);
    }

    public function test_closing_an_observation_records_its_closing_time_and_history(): void
    {
        $this->signInAs(Role::Agent);
        $observation = Observation::factory()->create();

        $this->patch(route('logbook.update', $observation), [
            'status' => ObservationStatus::Closed->value,
            'follow_up' => 'Navire rentré au port.',
        ])->assertRedirect(route('logbook.show', $observation));

        $this->assertNotNull($observation->fresh()->closed_at);
        $this->get(route('logbook.show', $observation))->assertOk()->assertSee('Navire rentré au port.');
    }
}
