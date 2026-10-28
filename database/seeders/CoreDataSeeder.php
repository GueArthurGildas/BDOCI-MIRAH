<?php

namespace Database\Seeders;

use App\Enums\UserKind;
use App\Models\AppModule;
use App\Models\User;
use App\Support\Core\ReferenceLists;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoreDataSeeder extends Seeder
{
    public const AI_AGENT_EMAIL = 'ai-agent@system.local';

    private const MODULES = [
        'M01' => 'vessels',
        'M02' => 'licenses',
        'M03' => 'tracking',
        'M04' => 'zee-declarations',
        'M05' => 'logbook',
        'M06' => 'port-state',
        'M07' => 'witness-reports',
        'M08' => 'infractions',
        'M09' => 'missions',
        'M10' => 'administration',
        'M11' => 'inspections',
        'M12' => 'communications',
    ];

    private const LOGBOOK_CHANNELS = [
        'phone' => ['fr' => 'Téléphone', 'en' => 'Phone'],
        'email' => ['fr' => 'E-mail', 'en' => 'E-mail'],
        'themis' => ['fr' => 'THEMIS', 'en' => 'THEMIS'],
        'radio' => ['fr' => 'Radio', 'en' => 'Radio'],
        'mail' => ['fr' => 'Courrier', 'en' => 'Mail'],
        'in_person' => ['fr' => 'En personne', 'en' => 'In person'],
    ];

    private const ZEE_DECLARATION_REASONS = [
        'eez_transit' => ['fr' => 'Transit dans la ZEE', 'en' => 'EEZ transit'],
        'eez_fishing' => ['fr' => 'Pêche dans la ZEE', 'en' => 'Fishing in the EEZ'],
        'transit_to_ivorian_port' => ['fr' => 'Transit vers un port ivoirien', 'en' => 'Transit to an Ivorian port'],
        'high_seas_fishing' => ['fr' => 'Pêche en haute mer', 'en' => 'High seas fishing'],
        'foreign_eez_fishing' => ['fr' => 'Pêche dans une ZEE étrangère', 'en' => 'Fishing in a foreign EEZ'],
    ];

    public function run(): void
    {
        foreach (self::MODULES as $code => $slug) {
            AppModule::updateOrCreate(['code' => $code], ['slug' => $slug]);
        }

        $lists = app(ReferenceLists::class);
        $lists->define('logbook_channel', self::LOGBOOK_CHANNELS);
        $lists->define('zee_declaration_reason', self::ZEE_DECLARATION_REASONS);

        $this->createAiAgent();
    }

    private function createAiAgent(): void
    {
        User::firstOrCreate(
            ['email' => self::AI_AGENT_EMAIL],
            [
                'name' => 'AI agent',
                'password' => Str::random(64),
                'is_active' => false,
            ],
        )->forceFill(['kind' => UserKind::AiAgent->value])->save();
    }
}
