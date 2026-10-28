<?php

namespace Database\Seeders;

use App\Enums\ActorRole;
use App\Enums\AssetType;
use App\Enums\InfractionSeverity;
use App\Models\Agent;
use App\Models\Asset;
use App\Models\AuthorizedListPublication;
use App\Models\ExternalInspection;
use App\Models\InfractionCase;
use App\Models\InfractionType;
use App\Models\LegalEntity;
use App\Models\License;
use App\Models\Mission;
use App\Models\Person;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselActor;
use App\Models\WitnessReport;
use App\Models\Zone;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use App\Support\Vessels\VesselRelationsSynchronizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Modules\Infractions\Actions\AddEvidenceItem;
use Modules\Infractions\Actions\CloseInfractionCase;
use Modules\Infractions\Actions\DecidePrecautionaryMeasure;
use Modules\Infractions\Actions\FileAppeal;
use Modules\Infractions\Actions\OpenInfractionCase;
use Modules\Infractions\Actions\OpenLitigationFollowUp;
use Modules\Infractions\Actions\ProposePrecautionaryMeasure;
use Modules\Infractions\Actions\RecordDecision;
use Modules\Infractions\Actions\RecordPayment;
use Modules\Infractions\Actions\RecordSeal;
use Modules\Infractions\Actions\RecordSeizure;
use Modules\Infractions\Actions\RecordViolationReport;
use Modules\Infractions\Actions\UpholdInfraction;
use Modules\Licenses\Actions\SuspendLicense;
use Modules\Missions\Actions\AddMissionEvent;
use Modules\Missions\Actions\AuthorizeMission;
use Modules\Missions\Actions\CompleteMission;
use Modules\Missions\Actions\ComputeMissionReview;
use Modules\Missions\Actions\ImportPatrolTrack;
use Modules\Missions\Actions\RecordAssetUsage;
use Modules\Missions\Actions\RecordObservedUnit;
use Modules\Missions\Actions\RecordObserverBoarding;
use Modules\Missions\Actions\SaveMissionBriefing;
use Modules\Missions\Actions\SaveMissionDebriefing;
use Modules\Missions\Actions\ScheduleMission;
use Modules\Missions\Actions\StartMission;
use Modules\Witnesses\Actions\DecideWitnessReport;
use Modules\Witnesses\Actions\RecordAuthorizedListDistribution;
use Modules\Witnesses\Actions\RecordWitnessReport;
use Modules\Witnesses\Actions\SaveReportInvestigation;
use Modules\Witnesses\Actions\SaveReportUsability;

class Phase6DemoSeeder extends Seeder
{
    private const SEVERITIES = [
        'INF-01' => InfractionSeverity::VerySerious,
        'INF-02' => InfractionSeverity::Serious,
        'INF-03' => InfractionSeverity::VerySerious,
        'INF-04' => InfractionSeverity::Serious,
        'INF-05' => InfractionSeverity::Minor,
        'INF-06' => InfractionSeverity::Serious,
        'INF-07' => InfractionSeverity::Minor,
    ];

    private const EXTRA_TYPES = [
        ['INF-08', 'Absence de journal de pêche à bord', 'Loi n° 2016-554, art. 56', InfractionSeverity::Minor],
        ['INF-09', 'Refus de contrôle ou d\'embarquement d\'un observateur', 'Loi n° 2016-554, art. 60', InfractionSeverity::Serious],
    ];

    private const ASSETS = [
        ['patrol_boat', 'Vedette de patrouille CSP 03'],
        ['intervention_boat', 'Vedette d\'intervention CSP 07'],
        ['vehicle', 'Pick-up de liaison Abidjan'],
        ['drone', 'Drone de surveillance DJI M300'],
        ['vhf', 'VHF portable marine (lot A)'],
        ['gps', 'GPS de poche Garmin (lot B)'],
        ['inspection_equipment', 'Mallette d\'inspection des engins'],
        ['seals', 'Lot de scellés numérotés SC-2000 à SC-2099'],
    ];

    private User $director;

    private User $teamLead;

    private User $agent;

    private ActionRunner $runner;

    public function run(ActionRunner $runner): void
    {
        $this->runner = $runner;
        $this->director = User::where('email', 'director@example.com')->firstOrFail();
        $this->teamLead = User::where('email', 'teamlead@example.com')->firstOrFail();
        $this->agent = User::where('email', 'agent@example.com')->firstOrFail();

        $this->seedNomenclature();
        $this->seedAssets();
        $reports = $this->seedWitnessReports();
        $missions = $this->seedMissions();
        $this->seedCases($missions, $reports);
        $this->seedDistributions();
    }

    private function seedNomenclature(): void
    {
        foreach (self::SEVERITIES as $code => $severity) {
            InfractionType::where('code', $code)->update(['severity' => $severity->value]);
        }

        foreach (self::EXTRA_TYPES as [$code, $label, $legalBasis, $severity]) {
            InfractionType::firstOrCreate(['code' => $code], ['label' => $label, 'legal_basis' => $legalBasis, 'severity' => $severity->value, 'is_active' => true]);
        }
    }

    private function seedAssets(): void
    {
        foreach (self::ASSETS as [$type, $label]) {
            Asset::firstOrCreate(['label' => $label], ['type' => $type, 'is_active' => true]);
        }
    }

    /** @return array<string, WitnessReport> */
    private function seedWitnessReports(): array
    {
        $vessels = Vessel::whereIn('name', ['FU YUAN YU 7', 'ALBACORA CINCO', 'DONG WON 301'])->get()->keyBy('name');

        $received = $this->runner->run(RecordWitnessReport::class, $this->agent, [
            'received_at' => now()->subDays(1)->setTime(9, 30),
            'channel' => 'phone',
            'latitude' => 4.52,
            'longitude' => -5.91,
            'witness_name' => 'Capitaine Yao Kouassi',
            'witness_category' => 'fisher',
            'witness_phone' => '+225 07 08 09 10 11',
            'vessels' => [[
                'name' => 'Navire sans pavillon visible',
                'type' => 'Palangrier',
                'other_marks' => 'Coque bleue, étrave blanche',
                'is_fishing' => true,
                'latitude' => 4.50,
                'longitude' => -5.95,
                'description' => 'Palangres filées à 12 milles de Sassandra, aucune lumière de pêche réglementaire.',
            ]],
        ]);

        $verified = $this->runner->run(RecordWitnessReport::class, $this->agent, [
            'received_at' => now()->subDays(6)->setTime(14, 10),
            'channel' => 'email',
            'latitude' => 5.02,
            'longitude' => -4.31,
            'witness_name' => 'Port autonome de San Pedro — capitainerie',
            'witness_category' => 'port_authority',
            'witness_email' => 'capitainerie@example.ci',
            'vessels' => [[
                'name' => 'FU YUAN YU 7',
                'flag' => 'CN',
                'type' => 'Palangrier',
                'ircs' => 'BXYZ7',
                'is_fishing' => true,
                'is_transshipping' => true,
                'description' => 'Transbordement de thon en mer avec un navire frigorifique à 20 milles de la côte.',
            ]],
        ]);
        $this->runner->run(SaveReportUsability::class, $this->teamLead, ['witness_report_id' => $verified->id, 'is_usable' => true, 'interview_report' => 'Entretien téléphonique avec le capitaine de port : date, heure et position concordantes.']);
        $this->runner->run(SaveReportInvestigation::class, $this->teamLead, ['witness_report_id' => $verified->id, 'elements' => ['themis', 'vms_extract', 'trajectories'], 'notes' => 'Trajectoire VMS cohérente avec une zone interdite entre 02 h et 05 h.']);
        $this->runner->run(DecideWitnessReport::class, $this->teamLead, ['witness_report_id' => $verified->id, 'decision' => 'forward_to_instruction']);

        $dismissed = $this->runner->run(RecordWitnessReport::class, $this->agent, [
            'received_at' => now()->subDays(12)->setTime(7, 45),
            'channel' => 'fax',
            'witness_name' => 'Marine nationale — veille côtière',
            'witness_category' => 'state_vessel',
            'vessels' => [['name' => 'ALBACORA CINCO', 'registration' => $vessels['ALBACORA CINCO']->registration_number, 'is_fishing' => false, 'description' => 'Navire en transit, aucun engin à l\'eau.']],
        ]);
        $this->runner->run(SaveReportUsability::class, $this->teamLead, ['witness_report_id' => $dismissed->id, 'is_usable' => false, 'interview_report' => 'Aucun élément d\'infraction.']);
        $this->runner->run(DecideWitnessReport::class, $this->teamLead, ['witness_report_id' => $dismissed->id, 'decision' => 'dismissal']);

        return ['received' => $received, 'forwarded' => $verified, 'dismissed' => $dismissed];
    }

    /** @return array<string, Mission> */
    private function seedMissions(): array
    {
        $agents = Agent::orderBy('id')->get();
        $assets = Asset::orderBy('id')->get();
        $vessels = Vessel::where('category', 'fishing')->orderBy('id')->get();
        $inspections = ExternalInspection::whereNotNull('vessel_id')->orderBy('id')->get();
        $fleet = fn (int $offset) => $vessels->slice($offset, 1)->first() ?? $vessels->first();

        $done = $this->schedule('maritime', 'internal', 'Patrouille de contrôle des chalutiers en mer territoriale', now()->subDays(20), 3, $agents, $assets, ['Zone économique exclusive', 'Mer territoriale']);
        $this->runner->run(AuthorizeMission::class, $this->director, ['mission_id' => $done->id]);
        $this->runner->run(SaveMissionBriefing::class, $this->teamLead, ['mission_id' => $done->id, 'held_at' => $done->planned_start_at->copy()->subHours(2), 'notes' => 'Consignes de sécurité et zones à couvrir.', 'checklist' => ['fuel', 'radio', 'gps', 'inspection_kit', 'documents']]);
        $this->runner->run(StartMission::class, $this->teamLead, ['mission_id' => $done->id]);
        $done->refresh();
        $done->update(['actual_start_at' => $done->planned_start_at]);
        $this->track($done);
        $this->runner->run(RecordObserverBoarding::class, $this->teamLead, ['mission_id' => $done->id, 'name' => 'Koffi', 'first_name' => 'Adjoua', 'vessel_id' => $fleet(0)->id, 'started_on' => $done->planned_start_at->toDateString()]);

        foreach ([[0, 'sighted', 'radar', 5.02, -4.31], [1, 'sighted', 'visual', 4.96, -4.88], [2, 'approached', 'visual', 4.81, -5.40], [3, 'approached', 'visual', 4.62, -6.02]] as [$offset, $level, $source, $latitude, $longitude]) {
            $this->runner->run(RecordObservedUnit::class, $this->teamLead, [
                'mission_id' => $done->id,
                'vessel_id' => $fleet($offset + 3)->id,
                'level' => $level,
                'sighting_source' => $source,
                'observed_activity' => $level === 'sighted' ? 'En route' : 'Pêche au chalut',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'observed_at' => $done->planned_start_at->copy()->addHours($offset + 2),
            ]);
        }

        if ($inspection = $inspections->first()) {
            $this->runner->run(RecordObservedUnit::class, $this->teamLead, [
                'mission_id' => $done->id,
                'vessel_id' => $inspection->vessel_id,
                'level' => 'inspected',
                'inspection_id' => $inspection->id,
                'observed_activity' => 'Contrôle à bord',
                'latitude' => 4.70,
                'longitude' => -5.85,
                'observed_at' => $done->planned_start_at->copy()->addHours(9),
            ]);
        }

        foreach ($done->missionAssets()->with('asset')->get()->filter(fn ($missionAsset) => $missionAsset->asset->type === AssetType::PatrolBoat) as $missionAsset) {
            $this->runner->run(RecordAssetUsage::class, $this->teamLead, ['mission_asset_id' => $missionAsset->id, 'engine_hours' => 36.5, 'nautical_miles' => 412.0, 'consumed_fuel_l' => 1850.0, 'state' => 'bon']);
        }

        $this->runner->run(AddMissionEvent::class, $this->teamLead, ['mission_id' => $done->id, 'occurred_at' => $done->planned_start_at->copy()->addHours(3), 'body' => 'Appareillage retardé par la houle, reprise de la route à 09 h.']);
        $this->runner->run(SaveMissionDebriefing::class, $this->teamLead, ['mission_id' => $done->id, 'notes' => 'Quatre navires approchés ou vus, un contrôle à bord.']);
        $this->runner->run(CompleteMission::class, $this->teamLead, ['mission_id' => $done->id]);
        $done->refresh();
        $done->update(['actual_end_at' => $done->planned_end_at]);
        $this->runner->run(ComputeMissionReview::class, $this->teamLead, ['mission_id' => $done->id]);

        $running = $this->schedule('lagoon', 'joint', 'Opération conjointe avec la Gendarmerie maritime en lagune Ébrié', now()->subHours(20), 1, $agents, $assets, ['Mer territoriale']);
        $this->runner->run(AuthorizeMission::class, $this->director, ['mission_id' => $running->id]);
        $this->runner->run(StartMission::class, $this->teamLead, ['mission_id' => $running->id]);
        $this->runner->run(RecordObservedUnit::class, $this->teamLead, ['mission_id' => $running->id, 'vessel_id' => $fleet(8)->id, 'level' => 'sighted', 'sighting_source' => 'visual', 'observed_activity' => 'Pirogue en pêche', 'latitude' => 5.28, 'longitude' => -4.02, 'observed_at' => now()->subHours(6)]);
        $this->runner->run(AddMissionEvent::class, $this->teamLead, ['mission_id' => $running->id, 'body' => 'Point de situation avec la Gendarmerie maritime.']);

        $authorized = $this->schedule('port', 'internal', 'Contrôle des débarquements au port de pêche de San Pedro', now()->addDays(2), 1, $agents, $assets, ['Mer territoriale']);
        $this->runner->run(AuthorizeMission::class, $this->director, ['mission_id' => $authorized->id]);

        $scheduled = $this->schedule('aerial_drone', 'internal', 'Surveillance de l\'aire marine protégée par drone', now()->addDays(6), 1, $agents, $assets, ['Aire marine protégée des îles Ehotilé']);

        return ['done' => $done->refresh(), 'running' => $running, 'authorized' => $authorized, 'scheduled' => $scheduled];
    }

    private function schedule(string $type, string $nature, string $objectives, $start, int $days, $agents, $assets, array $zones): Mission
    {
        $wanted = $type === 'aerial_drone' ? ['drone'] : ['patrol_boat', 'vhf', 'gps'];
        $assetIds = $assets->filter(fn (Asset $asset) => in_array($asset->type->value, $wanted, true))->pluck('id');

        return $this->runner->run(ScheduleMission::class, $this->teamLead, [
            'nature' => $nature,
            'type' => $type,
            'origin' => 'Programmation trimestrielle',
            'objectives' => $objectives,
            'planned_start_at' => $start,
            'planned_end_at' => $start->copy()->addDays($days),
            'zone_ids' => Zone::whereIn('name', $zones)->pluck('id')->all(),
            'points_gps' => "5.10, -4.20\n4.80, -5.50",
            'agent_ids' => $agents->take(3)->pluck('id')->all(),
            'lead_agent_id' => $agents->first()?->id,
            'asset_ids' => $assetIds->take(3)->all(),
        ]);
    }

    private function track(Mission $mission): void
    {
        $points = collect(range(0, 39))->map(fn (int $step) => sprintf('<trkpt lat="%.5f" lon="%.5f"></trkpt>', 5.10 - $step * 0.0175, -4.20 - $step * 0.0575))->implode('');
        $gpx = '<?xml version="1.0" encoding="UTF-8"?><gpx version="1.1" creator="CSP" xmlns="http://www.topografix.com/GPX/1/1"><trk><name>'.$mission->code.'</name><trkseg>'.$points.'</trkseg></trk></gpx>';
        $document = app(DocumentStore::class)->storeContent("trace_{$mission->code}.gpx", $gpx, 'application/gpx+xml', $this->teamLead);

        $this->runner->run(ImportPatrolTrack::class, $this->teamLead, ['mission_id' => $mission->id, 'document_id' => $document->id]);
    }

    /**
     * @param  array<string, Mission>  $missions
     * @param  array<string, WitnessReport>  $reports
     */
    private function seedCases(array $missions, array $reports): void
    {
        $vessels = Vessel::whereIn('name', ['DONG WON 301', 'ALBACORA CINCO', 'FU YUAN YU 7', 'AKWABA PECHE 1'])->get()->keyBy('name');
        $types = InfractionType::pluck('id', 'code');
        $inspections = ExternalInspection::whereNotNull('vessel_id')->get()->groupBy('vessel_id');
        Vessel::whereIn('id', $vessels->pluck('id'))->get()->each(fn (Vessel $vessel) => VesselRelationsSynchronizer::syncAll($vessel));
        $this->seedParties($vessels);

        $netting = $this->openCase($vessels['DONG WON 301'], 'Maillage inférieur au minimum réglementaire constaté lors du contrôle à quai.', [$types['INF-05']], now()->subDays(40), $inspections->get($vessels['DONG WON 301']->id)?->first()?->id);
        $this->runner->run(AddEvidenceItem::class, $this->teamLead, ['infraction_case_id' => $netting->id, 'description' => 'Mesure du maillage : 58 mm pour 70 mm requis (procès-verbal de mesure).']);
        $this->runner->run(RecordSeal::class, $this->teamLead, ['infraction_case_id' => $netting->id, 'number' => 'SC-2014', 'placed_at' => now()->subDays(40)]);
        $this->runner->run(RecordSeizure::class, $this->teamLead, ['infraction_case_id' => $netting->id, 'item' => 'Chalut de fond (jeu complet)', 'decision' => 'confiscation']);
        $measure = $this->runner->run(ProposePrecautionaryMeasure::class, $this->teamLead, ['infraction_case_id' => $netting->id, 'type' => 'gear_seizure']);
        $this->runner->run(DecidePrecautionaryMeasure::class, $this->director, ['measure_id' => $measure->id]);
        $this->runner->run(RecordViolationReport::class, $this->teamLead, ['infraction_case_id' => $netting->id, 'notified_at' => now()->subDays(39), 'is_signature_refused' => true]);
        $followUp = $this->runner->run(OpenLitigationFollowUp::class, $this->director, ['infraction_case_id' => $netting->id, 'type' => 'administrative_penalty']);
        $decision = $this->runner->run(RecordDecision::class, $this->director, ['follow_up_id' => $followUp->id, 'label' => 'Amende administrative et confiscation de l\'engin', 'is_penalty' => true]);
        $this->runner->run(RecordPayment::class, $this->director, ['decision_id' => $decision->id, 'amount' => 3500000, 'currency' => 'XOF', 'paid_on' => today()->subDays(20)]);
        $this->runner->run(FileAppeal::class, $this->director, ['decision_id' => $decision->id, 'filed_on' => today()->subDays(10)]);

        $this->openCase($vessels['DONG WON 301'], 'Pêche constatée dans la bande des 12 milles réservée à la pêche artisanale.', [$types['INF-03']], now()->subDays(8), null);

        $mission = $this->openCase($vessels['ALBACORA CINCO'], 'Balise VMS silencieuse pendant trois jours, constatée par la mission en mer.', [$types['INF-04']], now()->subDays(18), null, mission: $missions['done']->id);
        $this->runner->run(ProposePrecautionaryMeasure::class, $this->teamLead, ['infraction_case_id' => $mission->id, 'type' => 'port_services_refusal']);
        $this->runner->run(RecordViolationReport::class, $this->teamLead, ['infraction_case_id' => $mission->id, 'notified_at' => now()->subDays(17)]);
        $warning = $this->runner->run(OpenLitigationFollowUp::class, $this->director, ['infraction_case_id' => $mission->id, 'type' => 'warning']);
        $this->runner->run(RecordDecision::class, $this->director, ['follow_up_id' => $warning->id, 'label' => 'Avertissement écrit et mise en conformité de la balise', 'is_penalty' => false]);
        $this->runner->run(CloseInfractionCase::class, $this->director, ['infraction_case_id' => $mission->id]);

        $witness = $this->openCase($vessels['FU YUAN YU 7'], 'Transbordement en mer signalé par la capitainerie de San Pedro, trajectoire VMS dans une zone interdite.', [$types['INF-06'], $types['INF-03']], now()->subDays(5), null, report: $reports['forwarded']->id);
        $this->runner->run(AddEvidenceItem::class, $this->teamLead, ['infraction_case_id' => $witness->id, 'description' => 'Extraction VMS de la nuit du transbordement.']);
        $suspension = $this->runner->run(ProposePrecautionaryMeasure::class, $this->teamLead, ['infraction_case_id' => $witness->id, 'type' => 'license_suspension']);
        $this->runner->run(DecidePrecautionaryMeasure::class, $this->director, ['measure_id' => $suspension->id]);

        $license = License::where('vessel_id', $vessels['FU YUAN YU 7']->id)->where('status', 'active')->first() ?? License::factory()->create(['vessel_id' => $vessels['FU YUAN YU 7']->id, 'number' => 'LIC-2026-6001']);

        $this->runner->run(SuspendLicense::class, $this->director, [
            'license_id' => $license->id,
            'reason' => 'art_105',
            'started_on' => today()->toDateString(),
            'planned_end_on' => today()->addMonths(2)->toDateString(),
            'infraction_case_id' => $witness->id,
        ]);

        $this->openCase($vessels['AKWABA PECHE 1'], 'Capture de poissons sous-taille déclarée au débarquement.', [$types['INF-07']], now()->subDays(3), null);

        $this->runner->run(OpenInfractionCase::class, $this->teamLead, [
            'vessel_id' => $vessels['ALBACORA CINCO']->id,
            'facts' => 'Signalement non confirmé : aucun élément matériel.',
            'witness_report_id' => $reports['dismissed']->id,
            'opened_at' => now()->subDays(11),
        ]);
    }

    /** @param array<int, int> $typeIds */
    private function openCase(Vessel $vessel, string $facts, array $typeIds, $openedAt, ?int $inspectionId, ?int $mission = null, ?int $report = null): InfractionCase
    {
        $case = $this->runner->run(OpenInfractionCase::class, $this->teamLead, [
            'vessel_id' => $vessel->id,
            'facts' => $facts,
            'opened_at' => $openedAt,
            'inspection_id' => $inspectionId,
            'mission_id' => $mission,
            'witness_report_id' => $report,
            'qualification' => $inspectionId || $mission ? 'established' : 'presumed',
            'captain_observations' => 'Le capitaine conteste les faits et demande un nouvel examen.',
        ]);

        foreach ($typeIds as $typeId) {
            $this->runner->run(UpholdInfraction::class, $this->teamLead, ['infraction_case_id' => $case->id, 'infraction_type_id' => $typeId]);
        }

        return $case;
    }

    /** @param Collection<string, Vessel> $vessels */
    private function seedParties($vessels): void
    {
        $parties = [
            'DONG WON 301' => ['Kim', 'Jae-won', 'Dong Won Fisheries Abidjan'],
            'ALBACORA CINCO' => ['Etxebarria', 'Mikel', 'Albacora Pesca S.A.'],
            'FU YUAN YU 7' => ['Zhang', 'Wei', 'Fu Yuan Yu Marine Co.'],
            'AKWABA PECHE 1' => ['Kouadio', 'Jean-Baptiste', 'Akwaba Pêche SARL'],
        ];

        foreach ($parties as $name => [$lastName, $firstName, $company]) {
            $captain = Person::create(['name' => $lastName, 'first_name' => $firstName]);
            $owner = LegalEntity::create(['name' => $company, 'email' => 'contact@'.str($company)->ascii()->slug().'.example']);
            VesselActor::create(['vessel_id' => $vessels[$name]->id, 'role' => ActorRole::Captain, 'person_id' => $captain->id, 'started_on' => today()->subMonths(8)]);
            VesselActor::create(['vessel_id' => $vessels[$name]->id, 'role' => ActorRole::Owner, 'legal_entity_id' => $owner->id, 'started_on' => today()->subYears(2)]);
        }
    }

    private function seedDistributions(): void
    {
        $publication = AuthorizedListPublication::orderByDesc('version')->first();

        if (! $publication) {
            return;
        }

        foreach (['dpep@mrep.example.ci', 'capitainerie.sanpedro@example.ci'] as $recipient) {
            $this->runner->run(RecordAuthorizedListDistribution::class, $this->teamLead, ['authorized_list_publication_id' => $publication->id, 'recipient' => $recipient]);
        }
    }
}
