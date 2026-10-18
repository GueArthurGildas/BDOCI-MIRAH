<?php

namespace Modules\Infractions\Tests\Feature;

use App\Enums\InfractionStatus;
use App\Models\Infraction;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfractionPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_infractions_can_be_filtered_by_status(): void
    {
        $this->signInAs(Role::Inspector);
        Infraction::factory()->create(['reference' => 'PV-OPEN', 'status' => InfractionStatus::Reported]);
        Infraction::factory()->create(['reference' => 'PV-CLOSED', 'status' => InfractionStatus::Closed]);

        $this->get('/infractions?status=reported')->assertOk()->assertSee('PV-OPEN')->assertDontSee('PV-CLOSED');
    }

    public function test_inspectors_cannot_update_infractions(): void
    {
        $this->signInAs(Role::Inspector);
        $infraction = Infraction::factory()->create();

        $this->patch(route('infractions.update', $infraction), ['status' => 'closed'])->assertForbidden();
    }

    public function test_supervisors_record_decisions(): void
    {
        $this->signInAs(Role::Supervisor);
        $infraction = Infraction::factory()->create();

        $this->patch(route('infractions.update', $infraction), [
            'status' => InfractionStatus::Sanctioned->value,
            'decision' => 'Amende administrative.',
            'sanction' => '5 000 000 FCFA',
        ])->assertRedirect(route('infractions.show', $infraction));

        $this->assertSame(InfractionStatus::Sanctioned, $infraction->fresh()->status);
        $this->get(route('infractions.show', $infraction))->assertSee('5 000 000 FCFA');
    }
}
