<?php

namespace Modules\Communications\Tests\Feature;

use App\Enums\MessageTrigger;
use App\Enums\NoteRelay;
use App\Enums\NoteType;
use App\Models\InternalNote;
use App\Models\SentMessage;
use App\Models\Team;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Communications\Channels\WhatsAppGateway;
use Modules\Communications\Support\WhatsAppConsents;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC21')]
class InternalNotesTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'suspect',
            'vessel_id' => Vessel::factory()->create(['name' => 'KOUASSI 12'])->id,
            'urgency' => 'high',
            'recipient' => 'user:'.User::factory()->create()->id,
            'due_date' => today()->addDay()->toDateString(),
            'free_text' => 'Trajectoire inhabituelle.',
        ], $overrides);
    }

    public function test_an_agent_leaves_a_note_by_buttons_that_notifies_the_recipient_journals_and_audits(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $colleague = User::factory()->create();

        $this->post(route('communications.notes.store'), $this->payload(['recipient' => "user:{$colleague->id}"]))
            ->assertRedirect(route('communications.notes.index', ['box' => 'sent']));

        $note = InternalNote::sole();
        $this->assertSame(NoteType::Suspect, $note->type);
        $this->assertSame($agent->id, $note->author_id);
        $this->assertSame($colleague->id, $note->recipient_user_id);
        $this->assertFalse($note->is_read);
        $this->assertNull($note->relayed_by);

        $this->assertSame(1, $colleague->notifications()->count());
        $this->assertSame('communications::alerts.note.suspect', $colleague->notifications()->first()->data['translation_key']);
        $this->assertSame('KOUASSI 12', $colleague->notifications()->first()->data['params']['vessel']);

        $this->assertSame('note.created', VesselEvent::sole()->type);
        $this->assertSame($agent->id, Activity::where('event', 'communications.create_note')->sole()->causer_id);
    }

    public function test_a_note_without_vessel_is_allowed_and_leaves_no_vessel_event(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('communications.notes.store'), $this->payload(['vessel_id' => null, 'type' => 'reminder', 'free_text' => null, 'due_date' => null]))->assertRedirect();

        $this->assertNull(InternalNote::sole()->vessel_id);
        $this->assertSame(0, VesselEvent::count());
    }

    public function test_a_note_to_a_team_reaches_every_active_member_but_not_the_author(): void
    {
        $team = Team::factory()->create();
        $agent = $this->signInAs(Role::Agent, ['team_id' => $team->id]);
        $members = User::factory()->count(2)->create(['team_id' => $team->id]);
        $inactive = User::factory()->create(['team_id' => $team->id, 'is_active' => false]);

        $this->post(route('communications.notes.store'), $this->payload(['recipient' => "team:{$team->id}"]))->assertRedirect();

        $members->each(fn (User $member) => $this->assertSame(1, $member->notifications()->count()));
        $this->assertSame(0, $agent->notifications()->count());
        $this->assertSame(0, $inactive->notifications()->count());
    }

    #[Assumes('H-5-9')]
    public function test_a_note_can_be_relayed_by_email_to_the_recipient(): void
    {
        $this->signInAs(Role::Agent);
        $colleague = User::factory()->create();

        $this->post(route('communications.notes.store'), $this->payload(['recipient' => "user:{$colleague->id}", 'relay' => 'email']))->assertRedirect();

        $this->assertSame(NoteRelay::Email, InternalNote::sole()->relayed_by);
        $message = SentMessage::sole();
        $this->assertSame($colleague->email, $message->recipient);
        $this->assertSame(MessageTrigger::User, $message->triggered_by);
    }

    #[Assumes('H-5-2', 'H-5-9')]
    public function test_the_whatsapp_relay_needs_the_consent_of_the_recipient_number(): void
    {
        $this->signInAs(Role::Agent);
        $colleague = User::factory()->create(['whatsapp_number' => '+225 05 11 22 33 44']);
        $payload = $this->payload(['recipient' => "user:{$colleague->id}", 'relay' => 'whatsapp']);

        $this->post(route('communications.notes.store'), $payload);
        $this->assertNull(InternalNote::sole()->relayed_by);
        $this->assertSame(0, SentMessage::count());

        app(WhatsAppConsents::class)->grant('+225 05 11 22 33 44');
        $this->post(route('communications.notes.store'), $payload);

        $this->assertSame(NoteRelay::WhatsApp, InternalNote::latest('id')->first()->relayed_by);
        $this->assertSame('+2250511223344', app(WhatsAppGateway::class)->sent()[0]['number']);
    }

    public function test_the_buttons_input_is_validated(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('communications.notes.store'), $this->payload(['type' => 'gossip']))->assertSessionHasErrors('type');
        $this->post(route('communications.notes.store'), $this->payload(['urgency' => 'extreme']))->assertSessionHasErrors('urgency');
        $this->post(route('communications.notes.store'), $this->payload(['recipient' => 'user:99999']))->assertSessionHasErrors('recipient');
        $this->post(route('communications.notes.store'), $this->payload(['recipient' => 'everybody']))->assertSessionHasErrors('recipient');
        $this->post(route('communications.notes.store'), $this->payload(['due_date' => today()->subDay()->toDateString()]))->assertSessionHasErrors('due_date');
        $this->post(route('communications.notes.store'), $this->payload(['relay' => 'sms']))->assertSessionHasErrors('relay');
        $this->post(route('communications.notes.store'), $this->payload(['vessel_id' => 99999]))->assertSessionHasErrors('vessel_id');

        $this->assertSame(0, InternalNote::count());
    }

    public function test_the_admin_cannot_leave_notes(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('communications.notes.create'))->assertForbidden();
        $this->post(route('communications.notes.store'), $this->payload())->assertForbidden();
        $this->assertSame(0, InternalNote::count());
    }

    public function test_only_an_addressee_marks_a_note_as_read(): void
    {
        $recipient = $this->signInAs(Role::Agent);
        $note = InternalNote::factory()->create(['recipient_user_id' => $recipient->id]);
        $someoneElse = InternalNote::factory()->create();

        $this->post(route('communications.notes.read', $someoneElse))->assertForbidden();
        $this->assertFalse($someoneElse->fresh()->is_read);

        $this->post(route('communications.notes.read', $note))->assertRedirect();

        $this->assertTrue($note->fresh()->is_read);
        $this->assertNotNull($note->fresh()->read_at);
    }

    public function test_the_inbox_lists_notes_addressed_to_the_user_or_the_team_and_the_sent_box_the_authored_ones(): void
    {
        $team = Team::factory()->create();
        $user = $this->signInAs(Role::Agent, ['team_id' => $team->id]);
        InternalNote::factory()->create(['recipient_user_id' => $user->id, 'free_text' => 'DIRECT-NOTE']);
        InternalNote::factory()->create(['recipient_user_id' => null, 'recipient_team_id' => $team->id, 'free_text' => 'TEAM-NOTE']);
        InternalNote::factory()->create(['free_text' => 'OTHERS-NOTE']);
        InternalNote::factory()->create(['author_id' => $user->id, 'free_text' => 'MINE-NOTE']);

        $this->get(route('communications.notes.index'))->assertOk()
            ->assertSee('DIRECT-NOTE')->assertSee('TEAM-NOTE')->assertDontSee('OTHERS-NOTE')->assertDontSee('MINE-NOTE');
        $this->get(route('communications.notes.index', ['box' => 'sent']))->assertSee('MINE-NOTE')->assertDontSee('DIRECT-NOTE');
        $this->get(route('communications.notes.create'))->assertOk()->assertSee('btn-check', false);
    }

    public function test_the_vessel_is_preselected_from_the_current_screen(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->get(route('communications.notes.create', ['vessel' => $vessel->id]))->assertOk()
            ->assertSee('value="'.$vessel->id.'" selected', false);
    }
}
