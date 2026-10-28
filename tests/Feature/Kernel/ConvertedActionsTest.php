<?php

namespace Tests\Feature\Kernel;

use App\Enums\MovementDirection;
use App\Enums\ObservationStatus;
use App\Models\InfractionCase;
use App\Models\Observation;
use App\Models\Port;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ConvertedActionsTest extends TestCase
{
    use RefreshDatabase;

    private function assertJournaled(string $type, string $module, int $vesselId, string $auditCode, int $actorId): VesselEvent
    {
        $event = VesselEvent::where('type', $type)->sole();
        $this->assertSame($module, $event->module->code);
        $this->assertSame($vesselId, $event->vessel_id);
        $this->assertSame($actorId, $event->user_id);
        $this->assertSame($actorId, Activity::where('event', $auditCode)->latest('id')->firstOrFail()->causer_id);

        return $event;
    }

    public function test_registering_and_updating_a_vessel_write_the_journal_and_the_audit_log(): void
    {
        $actor = $this->signInAs(Role::TeamLead);
        $payload = ['name' => 'BANDAMA 7', 'registration_number' => 'AN-7777', 'flag' => 'CI', 'type' => 'trawler', 'category' => 'fishing', 'status' => 'active'];

        $this->post(route('vessels.store'), $payload)->assertRedirect();
        $vessel = Vessel::where('registration_number', 'AN-7777')->sole();
        $this->assertJournaled('vessel.registered', 'vessels', $vessel->id, 'vessels.register', $actor->id);

        $this->put(route('vessels.update', $vessel), [...$payload, 'owner' => 'Armement Ivoirien'])->assertRedirect();
        $this->assertJournaled('vessel.updated', 'vessels', $vessel->id, 'vessels.update', $actor->id);
        $this->assertSame('Armement Ivoirien', $vessel->fresh()->owner);
    }

    public function test_recording_a_movement_writes_the_journal_at_the_movement_date(): void
    {
        $actor = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        $occurredAt = now()->subHours(3)->startOfMinute();

        $this->post(route('movements.store'), [
            'vessel_id' => $vessel->id,
            'port_id' => Port::factory()->create()->id,
            'direction' => MovementDirection::Entry->value,
            'occurred_at' => $occurredAt->format('Y-m-d H:i'),
        ])->assertRedirect();

        $event = $this->assertJournaled('movement.recorded', 'movements', $vessel->id, 'movements.record', $actor->id);
        $this->assertTrue($occurredAt->equalTo($event->occurred_at));
    }

    public function test_opening_and_updating_an_observation_are_journaled_only_when_a_vessel_is_concerned(): void
    {
        $actor = $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $base = ['observed_at' => now()->subHour()->format('Y-m-d H:i'), 'level' => 'warning', 'description' => 'Rupture d\'émission.'];

        $this->post(route('logbook.store'), [...$base, 'vessel_id' => $vessel->id])->assertRedirect();
        $this->post(route('logbook.store'), $base)->assertRedirect();

        $this->assertSame(2, Observation::count());
        $this->assertSame(1, VesselEvent::where('type', 'observation.opened')->count());
        $this->assertJournaled('observation.opened', 'logbook', $vessel->id, 'logbook.open', $actor->id);

        $observation = Observation::where('vessel_id', $vessel->id)->sole();
        $this->patch(route('logbook.update', $observation), ['status' => ObservationStatus::Closed->value, 'follow_up' => 'Rentré au port.'])->assertRedirect();

        $this->assertNotNull($observation->fresh()->closed_at);
        $this->assertJournaled('observation.updated', 'logbook', $vessel->id, 'logbook.update', $actor->id);
    }

    public function test_updating_an_infraction_case_writes_the_journal_and_nothing_when_forbidden(): void
    {
        $case = InfractionCase::factory()->create();
        $payload = ['facts' => 'Faits corrigés.', 'qualification' => 'established'];

        $this->signInAs(Role::Agent);
        $this->patch(route('infractions.update', $case), $payload)->assertForbidden();
        $this->assertSame(0, VesselEvent::count());

        $actor = $this->signInAs(Role::TeamLead);
        $this->patch(route('infractions.update', $case), $payload)->assertRedirect();

        $this->assertJournaled('infraction.updated', 'infractions', $case->vessel_id, 'infractions.update', $actor->id);
    }
}
