<?php

namespace Modules\Communications\Tests\Feature;

use App\Enums\CommunicationChannelCode;
use App\Models\CommunicationChannel;
use App\Models\EmailConnector;
use App\Models\MessageTemplate;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Communications\Support\WhatsAppConsents;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC20', partial: true)]
class CommunicationsAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private function templatePayload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'vms-reminder',
            'channel' => 'email',
            'language' => 'fr',
            'subject' => 'Rappel {vessel_name}',
            'body' => 'Bonjour {vessel_name}',
            'form' => 'none',
            'is_active' => 1,
        ], $overrides);
    }

    public function test_a_team_lead_manages_message_templates_through_audited_actions(): void
    {
        $lead = $this->signInAs(Role::TeamLead);

        $this->post(route('communications.templates.store'), $this->templatePayload())->assertRedirect(route('communications.templates.index'));
        $template = MessageTemplate::sole();
        $this->put(route('communications.templates.update', $template), $this->templatePayload(['body' => 'Nouveau corps', 'is_active' => 0]))->assertRedirect();

        $this->assertSame('Nouveau corps', $template->fresh()->body);
        $this->assertFalse($template->fresh()->is_active);
        $this->assertSame($lead->id, Activity::where('event', 'communications.save_template')->latest('id')->first()->causer_id);
        $this->get(route('communications.templates.index'))->assertOk()->assertSee('vms-reminder');
        $this->get(route('communications.templates.edit', $template))->assertOk();
    }

    public function test_template_input_is_validated(): void
    {
        $this->signInAs(Role::TeamLead);
        MessageTemplate::factory()->create(['code' => 'taken']);

        $this->post(route('communications.templates.store'), $this->templatePayload(['code' => 'taken']))->assertSessionHasErrors('code');
        $this->post(route('communications.templates.store'), $this->templatePayload(['channel' => 'in_app']))->assertSessionHasErrors('channel');
        $this->post(route('communications.templates.store'), $this->templatePayload(['subject' => null]))->assertSessionHasErrors('subject');
        $this->post(route('communications.templates.store'), $this->templatePayload(['form' => 'form_99']))->assertSessionHasErrors('form');

        $this->assertSame(1, MessageTemplate::count());
    }

    public function test_agents_see_templates_but_cannot_change_them(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('communications.templates.index'))->assertOk();
        $this->get(route('communications.templates.create'))->assertForbidden();
        $this->post(route('communications.templates.store'), $this->templatePayload())->assertForbidden();
    }

    public function test_the_channels_page_lists_the_three_channels_with_safe_defaults(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('communications.channels.index'))->assertOk();

        $this->assertSame(3, CommunicationChannel::count());
        $this->assertTrue(CommunicationChannel::forCode(CommunicationChannelCode::WhatsApp)->is_consent_required);
        $this->assertFalse(CommunicationChannel::forCode(CommunicationChannelCode::WhatsApp)->is_auto_dispatch_enabled);
    }

    public function test_the_dispatch_manager_toggles_the_automatic_dispatch_of_a_channel(): void
    {
        $this->signInAs(Role::Director);
        $channel = CommunicationChannel::forCode(CommunicationChannelCode::Email);

        $this->put(route('communications.channels.update', $channel), ['is_auto_dispatch_enabled' => 1, 'is_consent_required' => 0])->assertRedirect();

        $this->assertTrue($channel->fresh()->is_auto_dispatch_enabled);
        $this->signInAs(Role::Agent);
        $this->put(route('communications.channels.update', $channel), ['is_auto_dispatch_enabled' => 0, 'is_consent_required' => 0])->assertForbidden();
    }

    #[Assumes('H-5-1')]
    public function test_only_the_administrator_configures_the_email_connector(): void
    {
        $payload = ['supplier' => 'Prestataire X', 'domain' => 'csp.example.ci', 'mailer' => 'log', 'is_active' => 1];

        $this->signInAs(Role::Director);
        $this->put(route('communications.channels.connector'), $payload)->assertForbidden();

        $this->signInAs(Role::Admin);
        $this->put(route('communications.channels.connector'), $payload)->assertRedirect(route('communications.channels.index'));
        $this->put(route('communications.channels.connector'), [...$payload, 'supplier' => 'Prestataire Y'])->assertRedirect();

        $connector = EmailConnector::sole();
        $this->assertSame('Prestataire Y', $connector->supplier);
        $this->assertSame('log', $connector->mailer());
        $this->put(route('communications.channels.connector'), [...$payload, 'mailer' => 'carrier-pigeon'])->assertSessionHasErrors('mailer');
    }

    #[Assumes('H-5-2')]
    public function test_consents_are_recorded_and_revoked_by_number(): void
    {
        $this->signInAs(Role::Agent);
        $consents = app(WhatsAppConsents::class);

        $this->post(route('communications.channels.consents.store'), ['number' => '+225 07 00 00 00 03', 'label' => 'Agent maritime'])->assertRedirect();
        $this->assertTrue($consents->isGranted('+2250700000003'));
        $this->get(route('communications.channels.index'))->assertSee('+2250700000003');

        $this->delete(route('communications.channels.consents.destroy'), ['number' => '+2250700000003'])->assertRedirect();
        $this->assertFalse($consents->isGranted('+2250700000003'));

        $this->post(route('communications.channels.consents.store'), ['number' => 'abc'])->assertSessionHasErrors('number');
    }
}
