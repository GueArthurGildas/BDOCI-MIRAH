<?php

namespace Database\Seeders;

use App\Enums\CommunicationChannelCode;
use App\Enums\DispatchMode;
use App\Enums\MessageForm;
use App\Enums\MessageLanguage;
use App\Enums\MessageStatus;
use App\Enums\MessageTrigger;
use App\Enums\NoteType;
use App\Enums\NoteUrgency;
use App\Models\CommunicationChannel;
use App\Models\DispatchRule;
use App\Models\EmailConnector;
use App\Models\InternalNote;
use App\Models\MessageTemplate;
use App\Models\PermissionGroup;
use App\Models\SentMessage;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Modules\Communications\Actions\RequestAiNote;
use Modules\Communications\Support\AiAgentAccount;
use Modules\Communications\Support\TriggerEvents;
use Modules\Communications\Support\WhatsAppConsents;

class Phase5DemoSeeder extends Seeder
{
    private const TEMPLATES = [
        ['vms-silence-reminder', CommunicationChannelCode::Email, MessageLanguage::French, 'Silence de la balise VMS : {vessel_name}', "Bonjour,\n\nLa balise VMS du navire {vessel_name} ({registration_number}) n'émet plus. Merci de vérifier l'équipement et de confirmer le retour des positions.\n\n{sender}", MessageForm::None],
        ['vms-silence-whatsapp', CommunicationChannelCode::WhatsApp, MessageLanguage::French, null, 'CSP : la balise VMS de {vessel_name} ({registration_number}) ne transmet plus. Merci de confirmer.', MessageForm::None],
        ['license-renewal-reminder', CommunicationChannelCode::Email, MessageLanguage::French, 'Échéance de licence : {vessel_name}', "Bonjour,\n\nLa licence du navire {vessel_name} ({registration_number}) arrive à échéance. Merci d'engager son renouvellement.\n\n{sender}", MessageForm::None],
        ['zee-declaration-receipt-foreign', CommunicationChannelCode::Email, MessageLanguage::English, 'Receipt of your EEZ declaration: {vessel_name}', "Dear Sir or Madam,\n\nWe acknowledge receipt of the EEZ declaration for {vessel_name} ({call_sign}) on {date}.\n\n{sender}", MessageForm::Form03Csp04],
        ['zee-declaration-receipt-national', CommunicationChannelCode::Email, MessageLanguage::French, 'Accusé de réception de votre déclaration : {vessel_name}', "Bonjour,\n\nNous accusons réception de la déclaration du navire {vessel_name} ({registration_number}) le {date}.\n\n{sender}", MessageForm::Form04Csp04],
    ];

    public function run(): void
    {
        $this->channels();
        $templates = $this->templates();
        app(AiAgentAccount::class)->provision();
        app(WhatsAppConsents::class)->grant('+225 07 00 00 00 01', 'Agent maritime, San Pedro');

        $this->rules($templates);
        $this->history();

        Artisan::call('communications:dispatch');
    }

    private function channels(): void
    {
        CommunicationChannel::forCode(CommunicationChannelCode::InApp)->update(['is_auto_dispatch_enabled' => true]);
        CommunicationChannel::forCode(CommunicationChannelCode::Email)->update(['is_auto_dispatch_enabled' => true]);
        CommunicationChannel::forCode(CommunicationChannelCode::WhatsApp);

        EmailConnector::create(['supplier' => 'Journal applicatif (développement)', 'domain' => 'csp.example.ci', 'parameters' => ['mailer' => 'log'], 'is_active' => true]);
    }

    /** @return array<string, MessageTemplate> */
    private function templates(): array
    {
        $templates = [];

        foreach (self::TEMPLATES as [$code, $channel, $language, $subject, $body, $form]) {
            $templates[$code] = MessageTemplate::create(compact('code', 'channel', 'language', 'subject', 'body', 'form') + ['is_active' => true]);
        }

        return $templates;
    }

    /** @param array<string, MessageTemplate> $templates */
    private function rules(array $templates): void
    {
        $group = PermissionGroup::where('code', 'automatic_dispatch')->first();

        DispatchRule::create([
            'message_template_id' => $templates['vms-silence-reminder']->id,
            'trigger_event' => TriggerEvents::BEACON_SILENT,
            'conditions' => ['recipient' => 'exploitation@armement-sapmer.example.ci'],
            'frequency_minutes' => 240,
            'daily_cap' => 3,
            'stop_condition' => 'acknowledged',
            'mode' => DispatchMode::Automatic,
            'is_active' => true,
            'editable_by_permission_group_id' => $group?->id,
        ]);

        DispatchRule::create([
            'message_template_id' => $templates['license-renewal-reminder']->id,
            'trigger_event' => TriggerEvents::LICENSE_EXPIRING,
            'conditions' => ['recipient' => 'licences@armateurs-abidjan.example.ci'],
            'frequency_minutes' => 10080,
            'daily_cap' => 5,
            'stop_condition' => null,
            'mode' => DispatchMode::WithValidation,
            'is_active' => true,
            'editable_by_permission_group_id' => $group?->id,
        ]);
    }

    private function history(): void
    {
        $vessels = Vessel::orderBy('id')->take(6)->get();
        $agent = User::where('email', 'agent@example.com')->first();
        $teamLead = User::where('email', 'teamlead@example.com')->first();
        $director = User::where('email', 'director@example.com')->first();
        $email = CommunicationChannel::forCode(CommunicationChannelCode::Email);

        $examples = [
            [MessageStatus::Acknowledged, 'capitaine.san-pedro@example.ci', 'Silence de la balise VMS'],
            [MessageStatus::Sent, 'agence.maritime@sassandra.example.ci', 'Échéance de licence'],
            [MessageStatus::Bounced, 'adresse-obsolete@example.ci', 'Rappel de déclaration ZEE'],
        ];

        foreach ($examples as $index => [$status, $recipient, $subject]) {
            SentMessage::factory()->status($status)->create([
                'communication_channel_id' => $email->id,
                'vessel_id' => $vessels[$index]->id,
                'recipient' => $recipient,
                'subject' => $subject,
                'body' => "{$subject} concernant le navire {$vessels[$index]->name}.",
                'triggered_by' => MessageTrigger::User,
                'user_id' => $agent->id,
                'reply_received' => $status === MessageStatus::Acknowledged,
            ]);
        }

        InternalNote::create(['type' => NoteType::ToCheck, 'vessel_id' => $vessels[3]->id, 'urgency' => NoteUrgency::High, 'recipient_user_id' => $teamLead->id, 'due_date' => now()->addDay(), 'free_text' => 'Navire vu hors couloir près de Sassandra, vérifier la dernière position.', 'author_id' => $agent->id]);
        InternalNote::create(['type' => NoteType::Reminder, 'vessel_id' => $vessels[4]->id, 'urgency' => NoteUrgency::Normal, 'recipient_team_id' => $agent->team_id, 'free_text' => "Relancer l'agent maritime pour la déclaration d'entrée.", 'author_id' => $teamLead->id]);
        InternalNote::create(['type' => NoteType::ToForward, 'vessel_id' => null, 'urgency' => NoteUrgency::Low, 'recipient_user_id' => $agent->id, 'free_text' => 'Transmettre la consigne du Directeur à la relève du soir.', 'author_id' => $director->id, 'is_read' => true, 'read_at' => now()->subHour()]);

        app(ActionRunner::class)->run(RequestAiNote::class, $director, [
            'type' => NoteType::Suspect->value,
            'vessel_id' => $vessels[5]->id,
            'urgency' => NoteUrgency::High->value,
            'recipient_user_id' => $teamLead->id,
            'recipient_team_id' => null,
            'due_date' => null,
            'relay' => null,
            'instruction' => 'Deux silences VMS en 48 heures sur ce navire : à surveiller de près.',
        ]);
    }
}
