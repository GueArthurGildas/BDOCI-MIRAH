<?php

namespace Database\Seeders;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageForm;
use App\Enums\MessageLanguage;
use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class AmrepMessageTemplatesSeeder extends Seeder
{
    private const TEMPLATES = [
        'amrep_entry_notice_fr' => ['fr', "AMREP : notification d'entrée au port du navire {vessel_name}", "Madame, Monsieur,\n\nConformément à l'Accord relatif aux mesures du ressort de l'État du port, nous vous notifions l'accès au port du navire {vessel_name} (immatriculation {registration_number}, indicatif d'appel {call_sign}), armateur : {owner}.\n\nDate : {date}\n\nCordialement,\n{sender}"],
        'amrep_inspection_notice_fr' => ['fr', 'AMREP : inspection du navire {vessel_name}', "Madame, Monsieur,\n\nUne inspection du navire {vessel_name} (immatriculation {registration_number}, indicatif d'appel {call_sign}), armateur : {owner}, a été réalisée dans le cadre de l'Accord relatif aux mesures du ressort de l'État du port. Les documents utiles sont joints à ce message.\n\nDate : {date}\n\nCordialement,\n{sender}"],
        'amrep_entry_denied_fr' => ['fr', "AMREP : refus d'accès au port du navire {vessel_name}", "Madame, Monsieur,\n\nNous vous informons que l'accès au port a été refusé au navire {vessel_name} (immatriculation {registration_number}, indicatif d'appel {call_sign}), armateur : {owner}, dans le cadre de l'Accord relatif aux mesures du ressort de l'État du port.\n\nDate : {date}\n\nCordialement,\n{sender}"],
        'amrep_entry_notice_en' => ['en', 'PSMA: port entry notification for vessel {vessel_name}', "Dear Sir or Madam,\n\nIn accordance with the Port State Measures Agreement, we notify you of the port entry of the vessel {vessel_name} (registration {registration_number}, call sign {call_sign}), owner: {owner}.\n\nDate: {date}\n\nBest regards,\n{sender}"],
        'amrep_inspection_notice_en' => ['en', 'PSMA: inspection of vessel {vessel_name}', "Dear Sir or Madam,\n\nAn inspection of the vessel {vessel_name} (registration {registration_number}, call sign {call_sign}), owner: {owner}, was carried out under the Port State Measures Agreement. The relevant documents are attached to this message.\n\nDate: {date}\n\nBest regards,\n{sender}"],
        'amrep_entry_denied_en' => ['en', 'PSMA: port entry denied to vessel {vessel_name}', "Dear Sir or Madam,\n\nWe inform you that port entry was denied to the vessel {vessel_name} (registration {registration_number}, call sign {call_sign}), owner: {owner}, under the Port State Measures Agreement.\n\nDate: {date}\n\nBest regards,\n{sender}"],
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as $code => [$language, $subject, $body]) {
            MessageTemplate::firstOrCreate(['code' => $code], [
                'channel' => CommunicationChannelCode::Email,
                'language' => MessageLanguage::from($language),
                'subject' => $subject,
                'body' => $body,
                'form' => MessageForm::None,
                'is_active' => true,
            ]);
        }
    }
}
