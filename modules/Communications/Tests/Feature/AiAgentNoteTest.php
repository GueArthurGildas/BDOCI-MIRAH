<?php

namespace Modules\Communications\Tests\Feature;

use App\Enums\AiAgentActionStatus;
use App\Models\AiAgentAction;
use App\Models\InternalNote;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Communications\Support\AiAgentAccount;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC27', partial: true)]
#[Assumes('H-5-8')]
class AiAgentNoteTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'to_check',
            'vessel_id' => Vessel::factory()->create()->id,
            'urgency' => 'normal',
            'recipient' => 'user:'.User::factory()->create()->id,
            'free_text' => 'Vérifier la licence de ce navire avant son départ.',
        ], $overrides);
    }

    public function test_the_ai_agent_creates_the_note_under_its_own_account_on_behalf_of_the_operator(): void
    {
        $director = $this->signInAs(Role::Director);
        $agent = app(AiAgentAccount::class)->provision();

        $this->post(route('communications.notes.ai'), $this->payload())->assertRedirect(route('communications.notes.index', ['box' => 'sent']))->assertSessionHas('status');

        $request = AiAgentAction::sole();
        $note = InternalNote::sole();
        $this->assertSame(AiAgentActionStatus::Executed, $request->status);
        $this->assertSame($director->id, $request->requested_by);
        $this->assertSame('communications.create_note', $request->action->code);
        $this->assertSame($note->id, $request->result_object_id);
        $this->assertSame($agent->id, $note->author_id);
        $this->assertSame($request->id, $note->ai_agent_action_id);
        $this->assertSame('Vérifier la licence de ce navire avant son départ.', $note->free_text);

        $audit = Activity::where('event', 'communications.create_note')->sole();
        $this->assertSame($agent->id, $audit->causer_id);
        $this->assertSame($director->id, $audit->properties['requested_by']);
        $this->assertSame(1, Activity::where('event', 'communications.request_ai_note')->where('causer_id', $director->id)->count());
    }

    public function test_the_request_is_refused_when_the_action_is_not_enabled_for_the_ai_agent(): void
    {
        $this->signInAs(Role::Director);
        $agent = app(AiAgentAccount::class)->provision();
        app(AiAgentAccount::class)->enable(Permission::CommunicationsCreateNote, false);

        $this->post(route('communications.notes.ai'), $this->payload())->assertRedirect()->assertSessionHas('error');

        $this->assertSame(AiAgentActionStatus::Refused, AiAgentAction::sole()->status);
        $this->assertSame(0, InternalNote::count());
        $this->assertNotNull($agent);
    }

    public function test_the_request_is_refused_when_no_ai_agent_account_exists(): void
    {
        $this->signInAs(Role::Director);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->post(route('communications.notes.ai'), $this->payload())->assertSessionHas('error');

        $this->assertSame(AiAgentActionStatus::Refused, AiAgentAction::sole()->status);
        $this->assertSame(0, InternalNote::count());
    }

    public function test_the_request_needs_the_dedicated_permission_and_never_acts_on_its_own(): void
    {
        $this->signInAs(Role::Agent);
        app(AiAgentAccount::class)->provision();

        $this->post(route('communications.notes.ai'), $this->payload())->assertForbidden();

        $this->assertSame(0, AiAgentAction::count());
        $this->assertSame(0, InternalNote::count());
    }

    public function test_the_instruction_is_required(): void
    {
        $this->signInAs(Role::Director);
        app(AiAgentAccount::class)->provision();

        $this->post(route('communications.notes.ai'), $this->payload(['free_text' => null]))->assertSessionHasErrors('instruction');

        $this->assertSame(0, AiAgentAction::count());
    }
}
