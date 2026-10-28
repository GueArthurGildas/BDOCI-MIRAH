<?php

namespace Modules\Communications\Tests\Feature;

use App\Enums\MessageStatus;
use App\Models\MessageTemplate;
use App\Models\SentMessage;
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

#[Covers('UC20', partial: true)]
class SendMessageTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'channel' => 'email',
            'message_template_id' => MessageTemplate::factory()->create()->id,
            'vessel_id' => Vessel::factory()->create(['name' => 'ALBACORA 7'])->id,
            'recipient' => 'armateur@example.ci',
        ], $overrides);
    }

    public function test_an_agent_sends_a_template_message_that_is_rendered_journaled_and_audited(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $payload = $this->payload();

        $this->post(route('communications.messages.store'), $payload)->assertRedirect();

        $message = SentMessage::sole();
        $this->assertSame(MessageStatus::Sent, $message->status);
        $this->assertNotNull($message->sent_at);
        $this->assertStringContainsString('ALBACORA 7', $message->subject);
        $this->assertStringContainsString($message->vessel->registration_number, $message->body);
        $this->assertSame($agent->id, $message->user_id);

        $event = VesselEvent::sole();
        $this->assertSame('message.sent', $event->type);
        $this->assertSame('communications', $event->module->code);
        $this->assertSame($agent->id, $event->user_id);
        $this->assertSame($agent->id, Activity::where('event', 'communications.send_message')->sole()->causer_id);
    }

    public function test_an_in_app_message_notifies_the_recipient_user(): void
    {
        $this->signInAs(Role::Agent);
        $colleague = User::factory()->create();

        $this->post(route('communications.messages.store'), $this->payload(['channel' => 'in_app', 'recipient' => $colleague->email]))->assertRedirect();

        $this->assertSame(MessageStatus::Sent, SentMessage::sole()->status);
        $this->assertSame(1, $colleague->notifications()->count());
        $this->assertSame('communications::alerts.message_received', $colleague->notifications()->first()->data['translation_key']);
    }

    #[Assumes('H-5-2')]
    public function test_whatsapp_is_refused_without_a_recorded_consent_and_sent_with_one(): void
    {
        $this->signInAs(Role::Agent);
        $template = MessageTemplate::factory()->whatsapp()->create();
        $payload = $this->payload(['channel' => 'whatsapp', 'message_template_id' => $template->id, 'recipient' => '+225 07 00 00 00 09']);

        $this->post(route('communications.messages.store'), $payload)->assertSessionHasErrors('recipient');
        $this->assertSame(0, SentMessage::count());
        $this->assertSame([], app(WhatsAppGateway::class)->sent());

        app(WhatsAppConsents::class)->grant('+225 07 00 00 00 09');
        $this->post(route('communications.messages.store'), $payload)->assertRedirect();

        $this->assertSame(MessageStatus::Sent, SentMessage::sole()->status);
        $this->assertSame('+2250700000009', app(WhatsAppGateway::class)->sent()[0]['number']);
    }

    #[Assumes('H-5-1')]
    public function test_the_email_goes_through_the_configured_mailer(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('communications.messages.store'), $this->payload())->assertRedirect();

        $this->assertSame(MessageStatus::Sent, SentMessage::sole()->status);
    }

    public function test_the_input_is_validated_and_nothing_is_written_on_failure(): void
    {
        $this->signInAs(Role::Agent);
        $whatsappTemplate = MessageTemplate::factory()->whatsapp()->create();

        $this->post(route('communications.messages.store'), $this->payload(['recipient' => 'not-an-email']))->assertSessionHasErrors('recipient');
        $this->post(route('communications.messages.store'), $this->payload(['message_template_id' => $whatsappTemplate->id]))->assertSessionHasErrors('message_template_id');
        $this->post(route('communications.messages.store'), $this->payload(['channel' => 'in_app', 'recipient' => 'ghost@example.ci']))->assertSessionHasErrors('recipient');
        $this->post(route('communications.messages.store'), $this->payload(['channel' => 'sms']))->assertSessionHasErrors('channel');
        $this->post(route('communications.messages.store'), $this->payload(['message_template_id' => 9999]))->assertSessionHasErrors('message_template_id');

        $this->assertSame(0, SentMessage::count());
        $this->assertSame(0, VesselEvent::count());
    }

    public function test_the_admin_cannot_send_or_read_messages(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('communications.messages.index'))->assertForbidden();
        $this->get(route('communications.messages.create'))->assertForbidden();
        $this->post(route('communications.messages.store'), $this->payload())->assertForbidden();
        $this->assertSame(0, SentMessage::count());
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('communications.messages.index'))->assertRedirect(route('login'));
    }

    public function test_the_message_list_and_detail_pages_render(): void
    {
        $this->signInAs(Role::Agent);
        $message = SentMessage::factory()->create(['recipient' => 'visible@example.ci']);

        $this->get(route('communications.messages.index'))->assertOk()->assertSee('visible@example.ci');
        $this->get(route('communications.messages.index', ['status' => 'failed']))->assertOk()->assertDontSee('visible@example.ci');
        $this->get(route('communications.messages.show', $message))->assertOk()->assertSee('visible@example.ci');
        $this->get(route('communications.messages.create'))->assertOk();
    }

    public function test_a_sent_message_is_acknowledged_once_with_the_reply_flag(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $message = SentMessage::factory()->create();

        $this->post(route('communications.messages.acknowledge', $message), ['reply_received' => 1])->assertRedirect();

        $message->refresh();
        $this->assertSame(MessageStatus::Acknowledged, $message->status);
        $this->assertNotNull($message->acknowledged_at);
        $this->assertTrue($message->reply_received);
        $this->assertSame('message.acknowledged', VesselEvent::sole()->type);
        $this->assertSame($agent->id, Activity::where('event', 'communications.acknowledge_message')->sole()->causer_id);

        $this->post(route('communications.messages.acknowledge', $message))->assertSessionHasErrors('message');
    }
}
