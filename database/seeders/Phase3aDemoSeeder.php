<?php

namespace Database\Seeders;

use App\Enums\LogbookDirection;
use App\Enums\OperationCategoryCode;
use App\Enums\WatchOrigin;
use App\Models\ConsistencyRule;
use App\Models\OperationCategory;
use App\Models\Rotation;
use App\Models\Team;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Logbook\LogbookCalendar;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\ObservationMirror;
use App\Support\Operations\WeatherScales;
use Illuminate\Database\Seeder;
use Modules\Logbook\Actions\AddLogbookEntry;
use Modules\Logbook\Actions\CloseLogbookDay;
use Modules\Logbook\Actions\RectifyLogbookEntry;
use Modules\Logbook\Actions\SetLogbookEntryHandling;
use Modules\Operations\Actions\AddOperationEvent;
use Modules\Operations\Actions\CloseOperation;
use Modules\Operations\Actions\GiveOperationInstruction;
use Modules\Operations\Actions\OpenOperation;
use Modules\Watch\Actions\AddVesselToDirectorList;
use Modules\Watch\Actions\CreateStandingInstruction;
use Modules\Watch\Actions\PlaceVesselUnderEnhancedMonitoring;
use Modules\Watch\Actions\PlanRotations;
use Modules\Watch\Actions\RecordSurveillanceObservation;
use Modules\Watch\Actions\StartShiftHandover;

class Phase3aDemoSeeder extends Seeder
{
    private const CATEGORIES = [
        'vms' => 'Balises VMS',
        'ers' => 'Journaux de pêche électroniques',
        'mep' => 'Mise en place de dispositifs',
        'inn' => 'Pêche illicite, non déclarée et non réglementée',
        'div' => 'Divers',
    ];

    private const CONSISTENCY_RULES = [
        ['emission_detected', 'Émission VMS ou AIS détectée sur le créneau'],
        ['speed_consistent', 'Vitesse cohérente avec l\'activité déclarée'],
        ['trajectory_compliant', 'Trajectoire conforme aux zones autorisées et à la distance des côtes'],
    ];

    private const PAST_DAYS = [
        3 => [
            ['phone', 'received', 'Appel de l\'agent maritime : arrivée retardée du chalutier au port d\'Abidjan, marée haute à 14 h.', false],
            ['email', 'received', 'Courriel de la DPEP : notification d\'escale pour San Pedro à confirmer sous 72 h.', false],
            ['radio', 'sent', 'Message radio à la vedette CSP : contrôle de routine de la zone de pêche artisanale à l\'ouest de Sassandra.', true],
        ],
        2 => [
            ['themis', 'received', 'Silence VMS signalé dans THEMIS pour un senneur battant pavillon espagnol au large de Tabou.', false],
            ['phone', 'sent', 'Rappel téléphonique à l\'armateur pour le retour de la balise.', false],
            ['in_person', 'received', 'Le capitaine d\'une pirogue est venu déclarer une avarie moteur devant Grand-Bassam.', false],
            ['mail', 'received', 'Courrier de l\'armement : demande de réexamen d\'une suspension de licence.', false],
        ],
        1 => [
            ['phone', 'received', 'Appel d\'un pêcheur : navire inconnu observé dans l\'aire marine protégée des îles Ehotilé.', false],
            ['radio', 'sent', 'Ordre à la vedette de rejoindre la zone et de relever l\'identité du navire.', true],
            ['themis', 'received', 'La liste de veille du Directeur a été mise à jour dans THEMIS.', false],
        ],
    ];

    public function run(): void
    {
        $runner = app(ActionRunner::class);
        $lead = User::where('email', 'teamlead@example.com')->first();
        $agent = User::where('email', 'agent@example.com')->first();

        if ($lead === null || $agent === null) {
            return;
        }

        $this->seedReferenceData();
        $this->assignTeams($lead, $agent);

        $vessels = Vessel::query()->where('category', 'fishing')->orderBy('id')->limit(12)->get();

        $this->seedRotations($runner, $lead);
        $operations = $this->seedOperations($runner, $lead, $vessels);
        $this->seedLogbook($runner, $lead, $agent, $vessels, $operations);
        $this->seedWatch($runner, $lead, $agent, $vessels);

        app(ObservationMirror::class)->syncAll();
    }

    private function seedReferenceData(): void
    {
        app(LogbookChannels::class)->ensure();
        app(WeatherScales::class)->ensure();

        foreach (OperationCategoryCode::cases() as $code) {
            OperationCategory::firstOrCreate(['code' => $code], ['label' => self::CATEGORIES[$code->value]]);
        }

        foreach (self::CONSISTENCY_RULES as [$code, $description]) {
            ConsistencyRule::firstOrCreate(['code' => $code], ['description' => $description]);
        }
    }

    private function assignTeams(User $lead, User $agent): void
    {
        $teams = Team::orderBy('id')->get();
        $lead->update(['team_id' => $teams[0]->id]);
        $agent->update(['team_id' => $teams[1]->id]);
        $teams[0]->update(['lead_user_id' => $lead->id]);
    }

    private function seedRotations(ActionRunner $runner, User $lead): void
    {
        $runner->run(PlanRotations::class, $lead, ['start_on' => today()->subDays(5)->toDateString(), 'days_count' => 16]);

        $rotation = Rotation::whereDate('day', today())->where('team_id', $lead->team_id)->first();

        if ($rotation) {
            $runner->run(StartShiftHandover::class, $lead, ['rotation_id' => $rotation->id]);
        }
    }

    private function seedOperations(ActionRunner $runner, User $lead, $vessels): array
    {
        $category = fn (OperationCategoryCode $code) => OperationCategory::where('code', $code)->firstOrFail()->id;

        $silence = $runner->run(OpenOperation::class, $lead, [
            'category_id' => $category(OperationCategoryCode::Vms),
            'name' => 'Silence balise senneur',
            'started_at' => now()->subDays(2)->toDateTimeString(),
            'origin' => 'THEMIS',
            'summary' => 'Silence VMS supérieur à 4 heures sans arrêt technique déclaré.',
            'vessel_ids' => [$vessels[3]->id],
            'first_event' => 'Alerte de silence reçue dans THEMIS.',
            'temperature' => 29.5,
            'pressure' => 1011.0,
            'visibility' => 'good',
            'cloud_cover' => 'partly_cloudy',
            'wind_beaufort' => 3,
            'sea_state' => 'slight',
        ]);
        $runner->run(AddOperationEvent::class, $lead, ['operation_id' => $silence->id, 'body' => 'Armateur contacté, retour de la balise promis sous 24 heures.', 'occurred_at' => now()->subDay()->toDateTimeString()]);
        $runner->run(GiveOperationInstruction::class, $lead, ['operation_id' => $silence->id, 'body' => 'Relancer l\'armateur à 8 h et signaler tout nouvel écart au Chef d\'équipe.']);
        $runner->run(CloseOperation::class, $lead, ['operation_id' => $silence->id, 'result' => 'La balise a repris ses émissions ; dossier classé.']);

        $protectedArea = $runner->run(OpenOperation::class, $lead, [
            'category_id' => $category(OperationCategoryCode::Inn),
            'name' => 'Navire inconnu aire protégée',
            'origin' => 'Appel d\'un pêcheur',
            'summary' => 'Navire non identifié observé dans l\'aire marine protégée des îles Ehotilé.',
            'vessel_ids' => [$vessels[5]->id],
            'first_event' => 'Appel téléphonique d\'un pêcheur de Grand-Bassam.',
        ]);
        $runner->run(AddOperationEvent::class, $lead, ['operation_id' => $protectedArea->id, 'body' => 'Vedette CSP en route vers la zone.']);

        $ers = $runner->run(OpenOperation::class, $lead, [
            'category_id' => $category(OperationCategoryCode::Ers),
            'name' => 'Journal de pêche manquant',
            'summary' => 'Journal de pêche électronique non transmis depuis trois jours.',
            'vessel_ids' => [$vessels[7]->id],
        ]);

        return [$silence, $protectedArea, $ers];
    }

    private function seedLogbook(ActionRunner $runner, User $lead, User $agent, $vessels, array $operations): void
    {
        $calendar = app(LogbookCalendar::class);
        $channels = app(LogbookChannels::class);
        $today = $calendar->today()->day;

        foreach (self::PAST_DAYS as $daysAgo => $lines) {
            $day = $today->copy()->subDays($daysAgo);

            foreach ($lines as $index => [$channel, $direction, $content, $isOrder]) {
                $entry = $runner->run(AddLogbookEntry::class, $lead, [
                    'occurred_at' => $day->copy()->setTime(9 + $index * 2, 15 * $index),
                    'channel_id' => $channels->idFor($channel),
                    'direction' => LogbookDirection::from($direction),
                    'content' => $content,
                    'is_order_or_decision' => $isOrder,
                    'vessel_ids' => $index === 0 ? [$vessels[$daysAgo]->id] : [],
                ]);
                $runner->run(SetLogbookEntryHandling::class, $lead, ['entry_id' => $entry->id, 'handling_status' => 'closed']);
            }

            $runner->run(CloseLogbookDay::class, $lead, ['day_id' => $calendar->dayFor($day->copy()->setTime(9, 0))->id]);
        }

        $now = now();
        $shiftStart = $calendar->today()->shift_start_at;
        $moment = fn (int $minutesAgo) => $now->copy()->subMinutes($minutesAgo)->max($shiftStart);
        $first = $runner->run(AddLogbookEntry::class, $agent, [
            'occurred_at' => $moment(50),
            'channel_id' => $channels->idFor('themis'),
            'content' => 'Navire sans émission VMS depuis 5 heures, hors zone de port.',
            'vessel_ids' => [$vessels[3]->id],
            'operation_id' => $operations[0]->id,
        ]);
        $runner->run(AddLogbookEntry::class, $agent, [
            'occurred_at' => $moment(30),
            'channel_id' => $channels->idFor('phone'),
            'content' => 'Appel d\'un pêcheur : navire non identifié dans l\'aire marine protégée.',
            'vessel_ids' => [$vessels[5]->id],
            'operation_id' => $operations[1]->id,
            'is_order_or_decision' => false,
        ]);
        $runner->run(AddLogbookEntry::class, $lead, [
            'occurred_at' => $moment(10),
            'channel_id' => $channels->idFor('radio'),
            'direction' => LogbookDirection::Sent,
            'content' => 'Ordre à la vedette CSP 02 de rejoindre l\'aire protégée.',
            'is_order_or_decision' => true,
            'operation_id' => $operations[1]->id,
        ]);
        $runner->run(RectifyLogbookEntry::class, $lead, [
            'entry_id' => $first->id,
            'content' => 'Rectification : silence VMS de 4 heures et non de 5 heures.',
        ]);
    }

    private function seedWatch(ActionRunner $runner, User $lead, User $agent, $vessels): void
    {
        $teams = Team::orderBy('id')->get();

        $runner->run(AddVesselToDirectorList::class, $lead, ['vessel_id' => $vessels[8]->id]);
        $runner->run(AddVesselToDirectorList::class, $lead, ['vessel_id' => $vessels[9]->id]);
        $runner->run(PlaceVesselUnderEnhancedMonitoring::class, $lead, ['vessel_id' => $vessels[10]->id, 'origin' => WatchOrigin::EnhancedMonitoring->value]);

        $runner->run(CreateStandingInstruction::class, $lead, ['body' => 'Contrôler chaque jour la fraîcheur de la liste des navires autorisés avant la prise de service.']);
        $runner->run(CreateStandingInstruction::class, $lead, ['vessel_id' => $vessels[10]->id, 'body' => 'Ne pas autoriser de sortie sans contact préalable avec le Chef d\'équipe.']);

        foreach (['Rupture d\'émission constatée au créneau de 10 h.', 'Émission rétablie mais vitesse incohérente.', 'Trajectoire proche de la zone interdite.', 'Position encore proche de la zone interdite.'] as $body) {
            $runner->run(RecordSurveillanceObservation::class, $agent, [
                'vessel_id' => $vessels[11]->id,
                'body' => $body,
                'relay_to_team_id' => $teams[2]->id,
            ]);
        }
    }
}
