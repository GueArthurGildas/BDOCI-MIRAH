<?php

namespace Modules\Movements\Tests\Feature;

use App\Enums\MovementDirection;
use App\Models\Movement;
use App\Models\Port;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_movements_can_be_filtered_by_direction(): void
    {
        $this->signInAs(Role::SurveillanceAgent);
        Movement::factory()->create(['direction' => MovementDirection::Entry, 'reason' => 'Débarquement de thon']);
        Movement::factory()->create(['direction' => MovementDirection::Exit, 'reason' => 'Départ en marée']);

        $this->get('/movements?direction=entry')
            ->assertOk()
            ->assertSee('Débarquement de thon')
            ->assertDontSee('Départ en marée');
    }

    public function test_agents_record_movements(): void
    {
        $agent = $this->signInAs(Role::SurveillanceAgent);

        $this->post('/movements', [
            'vessel_id' => Vessel::factory()->create()->id,
            'port_id' => Port::factory()->create()->id,
            'direction' => MovementDirection::Entry->value,
            'occurred_at' => now()->subMinutes(30)->format('Y-m-d H:i'),
        ])->assertRedirect(route('movements.index'));

        $this->assertSame($agent->id, Movement::sole()->recorded_by);
    }

    public function test_inspectors_cannot_record_movements(): void
    {
        $this->signInAs(Role::Inspector);

        $this->get('/movements/create')->assertForbidden();
    }
}
