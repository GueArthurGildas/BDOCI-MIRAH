<?php

namespace Modules\Operations\Tests\Feature;

use App\Enums\OperationStatus;
use App\Enums\WatchOrigin;
use App\Models\LogbookEntry;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\WatchEntry;
use App\Support\Authorization\Role;
use App\Support\Watch\WatchList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class OperationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_and_administrators_are_forbidden(): void
    {
        $this->get(route('operations.index'))->assertRedirect(route('login'));

        $this->signInAs(Role::Admin);
        $this->get(route('operations.index'))->assertForbidden();
    }

    #[Covers('UC11')]
    #[Assumes('H-3a-8')]
    public function test_an_agent_opens_an_operation_with_its_context_vessels_and_journal(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $category = OperationCategory::factory()->create();
        $vessel = Vessel::factory()->create();

        $this->post(route('operations.store'), [
            'category_id' => $category->id,
            'name' => 'Silence balise',
            'origin' => 'THEMIS',
            'summary' => 'Silence supérieur à 4 heures.',
            'vessel_ids' => [$vessel->id],
            'first_event' => 'Alerte reçue.',
            'temperature' => 29.5,
            'wind_beaufort' => 4,
            'visibility' => 'good',
        ])->assertRedirect();

        $operation = Operation::sole();
        $this->assertSame(OperationStatus::Open, $operation->status);
        $this->assertSame($agent->id, $operation->created_by);
        $this->assertSame([$vessel->id], $operation->vessels->pluck('id')->all());
        $this->assertSame($agent->id, $operation->personnel->sole()->id);
        $this->assertSame(4, $operation->context->wind_beaufort);
        $this->assertSame('operation.opened', VesselEvent::sole()->type);
        $this->assertSame($agent->id, Activity::where('event', 'operations.open')->sole()->causer_id);

        $this->get(route('operations.show', $operation))->assertOk()->assertSee($operation->code);
    }

    #[Covers('UC11', partial: true)]
    public function test_opening_validates_the_input_and_writes_nothing_when_invalid(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('operations.store'), ['category_id' => 999, 'name' => '', 'wind_beaufort' => 15, 'visibility' => 'unknown'])
            ->assertSessionHasErrors(['category_id', 'name', 'wind_beaufort', 'visibility']);

        $this->assertSame(0, Operation::count());
        $this->assertDatabaseCount('operation_counters', 0);
    }

    #[Covers('UC11')]
    public function test_following_an_operation_adds_events_and_instructions_then_the_team_lead_closes_it(): void
    {
        $this->signInAs(Role::Agent);
        $operation = Operation::factory()->create();
        $vessel = Vessel::factory()->create();
        $operation->vessels()->attach($vessel);

        $this->post(route('operations.events.store', $operation), ['body' => 'Armateur contacté.'])->assertRedirect();
        $this->assertSame(OperationStatus::InProgress, $operation->fresh()->status);
        $this->assertSame('Armateur contacté.', $operation->events()->sole()->body);

        $this->post(route('operations.instructions.store', $operation), ['body' => 'Relancer'])->assertForbidden();

        $lead = $this->signInAs(Role::TeamLead);
        $this->post(route('operations.instructions.store', $operation), ['body' => 'Relancer l\'armateur à 8 h.'])->assertRedirect();
        $this->assertSame($lead->id, $operation->instructions()->sole()->given_by);

        $this->post(route('operations.close', $operation), ['result' => ''])->assertSessionHasErrors('result');
        $this->post(route('operations.close', $operation), ['result' => 'Balise réparée.'])->assertRedirect();

        $operation->refresh();
        $this->assertSame(OperationStatus::Closed, $operation->status);
        $this->assertSame('Balise réparée.', $operation->result);
        $this->assertNotNull($operation->ended_at);
        $this->assertSame($lead->id, $operation->closed_by);
        $this->assertSame('operation.closed', VesselEvent::latest('id')->first()->type);
        $this->assertSame($lead->id, Activity::where('event', 'operations.close')->sole()->causer_id);
    }

    #[Covers('UC11', partial: true)]
    public function test_agents_cannot_close_an_operation(): void
    {
        $this->signInAs(Role::Agent);
        $operation = Operation::factory()->create();

        $this->post(route('operations.close', $operation), ['result' => 'Fini.'])->assertForbidden();

        $this->assertSame(OperationStatus::Open, $operation->fresh()->status);
    }

    #[Covers('UC11', partial: true)]
    public function test_a_closed_operation_refuses_further_changes(): void
    {
        $this->signInAs(Role::TeamLead);
        $operation = Operation::factory()->create(['status' => OperationStatus::Closed]);

        $this->post(route('operations.events.store', $operation), ['body' => 'Trop tard'])->assertSessionHasErrors('body');
        $this->post(route('operations.instructions.store', $operation), ['body' => 'Trop tard'])->assertSessionHasErrors('body');
        $this->post(route('operations.close', $operation), ['result' => 'Encore'])->assertSessionHasErrors('result');

        $this->assertSame(0, $operation->events()->count());
    }

    #[Covers('UC11', 'UC26', partial: true)]
    #[Assumes('H-3a-7')]
    public function test_closing_an_operation_removes_its_vessels_from_the_watch_list(): void
    {
        $this->signInAs(Role::TeamLead);
        $operation = Operation::factory()->create();
        $vessel = Vessel::factory()->create();
        $other = Vessel::factory()->create();
        app(WatchList::class)->open($vessel, WatchOrigin::Logbook, operation: $operation);
        app(WatchList::class)->open($other, WatchOrigin::DirectorListManual);

        $this->post(route('operations.close', $operation), ['result' => 'Régularisé.']);

        $this->assertSame(0, WatchEntry::open()->where('vessel_id', $vessel->id)->count());
        $this->assertSame(1, WatchEntry::open()->where('vessel_id', $other->id)->count());
    }

    #[Covers('UC11', 'UC12', partial: true)]
    public function test_an_operation_gathers_the_logbook_lines_that_refer_to_it(): void
    {
        $this->signInAs(Role::Agent);
        $operation = Operation::factory()->create();
        LogbookEntry::factory()->create(['operation_id' => $operation->id, 'content' => 'Ligne rattachée à l\'opération']);

        $this->get(route('operations.show', $operation))->assertOk()->assertSee('Ligne rattachée à l\'opération');
    }

    public function test_the_operations_list_can_be_filtered(): void
    {
        $this->signInAs(Role::Agent);
        $category = OperationCategory::factory()->create();
        Operation::factory()->create(['name' => 'Alpha visible', 'category_id' => $category->id, 'status' => OperationStatus::Open]);
        Operation::factory()->create(['name' => 'Bravo masquée', 'status' => OperationStatus::Closed]);

        $this->get(route('operations.index', ['status' => 'open']))->assertOk()->assertSee('Alpha')->assertDontSee('Bravo');
        $this->get(route('operations.index', ['category' => $category->id]))->assertOk()->assertSee('Alpha')->assertDontSee('Bravo');
        $this->get(route('operations.index', ['search' => 'Bravo']))->assertOk()->assertSee('Bravo')->assertDontSee('Alpha');
    }
}
