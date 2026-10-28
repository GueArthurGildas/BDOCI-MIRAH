<?php

namespace Database\Seeders;

use App\Enums\ActorRole;
use App\Enums\ExternalListType;
use App\Enums\FleetSegment;
use App\Enums\FleetType;
use App\Enums\IdentifierType;
use App\Enums\LegalStatus;
use App\Enums\MatchingStatus;
use App\Enums\SourceSystem;
use App\Enums\VesselCategory;
use App\Enums\VesselType;
use App\Enums\ZoneRuleSegment;
use App\Models\Accreditation;
use App\Models\Agent;
use App\Models\Contact;
use App\Models\Country;
use App\Models\ExternalList;
use App\Models\Gear;
use App\Models\Institution;
use App\Models\LegalEntity;
use App\Models\Person;
use App\Models\Port;
use App\Models\Presentation;
use App\Models\SourcePriority;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselActor;
use App\Models\VesselFieldProvenance;
use App\Models\VesselHistory;
use App\Models\VesselSource;
use App\Models\VesselTypeRecord;
use App\Models\Zone;
use App\Models\ZoneRule;
use App\Support\Actions\ActionRunner;
use App\Support\Vessels\VesselRelationsSynchronizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Modules\Vessels\Actions\IngestVesselSource;

class Phase2DemoSeeder extends Seeder
{
    private const INSTITUTIONS = [
        ['rfmo', 'ICCAT - Commission internationale pour la conservation des thonidés de l\'Atlantique', null],
        ['rfb', 'COREP - Comité des pêches du Golfe de Guinée', null],
        ['fao', 'FAO - Organisation des Nations unies pour l\'alimentation et l\'agriculture', null],
        ['authority', 'Ministère des Ressources animales et halieutiques', 'CI'],
        ['state', 'Direction des pêches du Ghana', 'GH'],
        ['state', 'Direction des pêches maritimes du Sénégal', 'SN'],
    ];

    private const VESSEL_TYPES = [
        ['TRW', 'Chalutiers'], ['PS', 'Senneurs à senne coulissante'], ['LL', 'Palangriers'], ['GN', 'Fileyeurs'],
        ['CAN', 'Pirogues'], ['SUP', 'Navires d\'appui'], ['REF', 'Navires frigorifiques'], ['PAT', 'Vedettes de surveillance'],
    ];

    private const GEARS = [
        ['OTB', 'Chalut de fond à panneaux'], ['PS', 'Senne coulissante'], ['LLS', 'Palangre calée'], ['GNS', 'Filet maillant calé'],
        ['LA', 'Lampara'], ['LHP', 'Lignes à main'], ['FPO', 'Nasses'],
    ];

    private const PRESENTATIONS = [
        ['WHL', 'Entier', 1.000], ['GUT', 'Éviscéré', 1.100], ['GUH', 'Éviscéré, étêté', 1.300], ['FIL', 'Filet', 2.600], ['DRZ', 'Habillé', 1.500],
    ];

    private const EXTRA_SPECIES = [
        ['ALB', 'Germon', 'Thunnus alalunga'], ['LTA', 'Thonine', 'Euthynnus alletteratus'], ['FRI', 'Auxide', 'Auxis thazard'],
        ['DOL', 'Coryphène commune', 'Coryphaena hippurus'], ['BAR', 'Barracudas', 'Sphyraena spp'],
    ];

    private const LEGAL_ENTITIES = [
        ['Société Ivoirienne d\'Armement de Pêche (SIAP)', 'Zone portuaire, Treichville, Abidjan', 'CI'],
        ['Pêcheries de l\'Ouest SARL', 'Boulevard maritime, San Pedro', 'CI'],
        ['Armement Bandama SA', 'Vridi canal, Abidjan', 'CI'],
        ['Coopérative des pêcheurs de Locodjro', 'Locodjro, Abidjan', 'CI'],
        ['Atunes del Atlántico SL', 'Puerto de Vigo', 'ES'],
        ['Compagnie Française du Thon Océanique', 'Concarneau', 'FR'],
        ['Zhoushan Ocean Fishing Co.', 'Zhoushan, Zhejiang', 'CN'],
        ['Ghana Tuna Fleet Ltd', 'Tema Fishing Harbour', 'GH'],
        ['Agence Maritime Abidjan Transit', 'Plateau, Abidjan', 'CI'],
    ];

    private const PEOPLE = [
        ['Kouassi', 'Yao', 'CI'], ['Traoré', 'Mamadou', 'CI'], ['Koffi', 'Ange', 'CI'], ['Diallo', 'Seydou', 'SN'],
        ['Mensah', 'Kwame', 'GH'], ['Etxeberria', 'Iker', 'ES'], ['Le Goff', 'Yann', 'FR'],
    ];

    private const AGENTS = [
        ['Bamba Fatoumata', 'Inspecteur principal', 'CSP', true], ['N\'Guessan Paul', 'Inspecteur', 'CSP', true],
        ['Aka Marie-Claire', 'Agent de surveillance', 'CSP', false], ['Gnahoré Serge', 'Officier de marine', 'Marine nationale', true],
    ];

    private const CONTACTS = [
        ['Autorité de pêche', 'GH', 'Direction des pêches du Ghana', 'fisheries@fishcom.example.gh', '+233 30 000 0000'],
        ['Autorité de pêche', 'SN', 'Direction des pêches maritimes', 'dpm@peche.example.sn', '+221 33 000 0000'],
        ['ORGP', null, 'Secrétariat ICCAT', 'info@iccat.example', '+34 91 000 0000'],
        ['Garde-côtes', 'LR', 'Garde-côtes du Liberia', 'coastguard@example.lr', '+231 77 000 0000'],
    ];

    private Collection $vessels;

    public function run(): void
    {
        $dispatcher = app('events');
        Model::setEventDispatcher($dispatcher);

        try {
            $this->seedReferences();
            $this->seedDirectory();
            $this->enrichFleet();
            $this->seedExternalLists();
            $this->seedSourcesAndDuplicates();
        } finally {
            Model::unsetEventDispatcher();
        }
    }

    private function seedReferences(): void
    {
        foreach (config('surveillance.flag_states') as $iso2) {
            Country::forIso2($iso2);
        }

        foreach (self::INSTITUTIONS as [$type, $name, $iso2]) {
            Institution::create(['type' => $type, 'name' => $name, 'country_id' => Country::forIso2($iso2)?->id]);
        }

        foreach (self::VESSEL_TYPES as [$code, $label]) {
            VesselTypeRecord::create(['code' => $code, 'label' => $label]);
        }

        foreach (self::GEARS as [$code, $label]) {
            Gear::create(['code_fao' => $code, 'label' => $label]);
        }

        foreach (self::PRESENTATIONS as [$code, $label, $factor]) {
            Presentation::create(['code' => $code, 'label' => $label, 'conversion_factor' => $factor]);
        }

        foreach (self::EXTRA_SPECIES as [$code, $name, $scientificName]) {
            Species::firstOrCreate(['fao_code' => $code], ['name' => $name, 'scientific_name' => $scientificName]);
        }

        $exclusiveZone = Zone::where('name', 'Zone économique exclusive')->first();

        if ($exclusiveZone) {
            foreach ([[ZoneRuleSegment::Artisanal, 1], [ZoneRuleSegment::SmallPelagics, 3], [ZoneRuleSegment::Trawl, 4], [ZoneRuleSegment::TunaVessel, 12]] as [$segment, $miles]) {
                ZoneRule::create(['zone_id' => $exclusiveZone->id, 'segment' => $segment, 'nautical_miles' => $miles]);
            }
        }

        $order = config('registry.default_source_order');

        foreach (config('registry.tracked_vessel_fields') as $field) {
            foreach ($order as $index => $source) {
                SourcePriority::create(['field' => $field, 'source' => $source, 'rank' => $index + 1]);
            }
        }
    }

    private function seedDirectory(): void
    {
        foreach (self::LEGAL_ENTITIES as [$name, $address, $iso2]) {
            LegalEntity::create(['name' => $name, 'address' => $address, 'country_id' => Country::forIso2($iso2)->id, 'email' => 'contact@'.str($name)->ascii()->slug().'.example']);
        }

        foreach (self::PEOPLE as [$name, $firstName, $iso2]) {
            Person::create(['name' => $name, 'first_name' => $firstName, 'nationality_country_id' => Country::forIso2($iso2)->id]);
        }

        $inspectorUsers = User::orderBy('id')->limit(count(self::AGENTS))->get();

        foreach (self::AGENTS as $index => [$name, $grade, $agency, $isInspector]) {
            $agent = Agent::create(['user_id' => $inspectorUsers[$index]->id ?? null, 'name' => $name, 'grade' => $grade, 'agency' => $agency, 'is_inspector' => $isInspector]);

            if ($isInspector) {
                Accreditation::create([
                    'agent_id' => $agent->id,
                    'card_number' => sprintf('CSP-%04d', 100 + $index),
                    'decision' => 'Décision d\'habilitation n° '.(2024 + $index % 2).'/'.(10 + $index),
                    'trainings' => "Inspection à bord\nSystèmes de suivi des navires",
                ]);
            }
        }

        foreach (self::CONTACTS as [$category, $iso2, $organization, $email, $phone]) {
            Contact::create(['category' => $category, 'country_id' => Country::forIso2($iso2)?->id, 'organization' => $organization, 'email' => $email, 'phone' => $phone]);
        }

    }

    private function enrichFleet(): void
    {
        $this->vessels = Vessel::orderBy('id')->get();
        $types = VesselTypeRecord::pluck('id', 'code');
        $ports = Port::whereIn('code', ['CIABJ', 'CISPY', 'CISAS'])->pluck('id')->values();
        $nationalOwners = LegalEntity::whereHas('country', fn ($countries) => $countries->where('iso2_code', 'CI'))->orderBy('id')->get();
        $foreignOwners = LegalEntity::whereHas('country', fn ($countries) => $countries->where('iso2_code', '!=', 'CI'))->orderBy('id')->get();
        $localAgent = LegalEntity::where('name', 'like', 'Agence Maritime%')->first();
        $people = Person::orderBy('id')->get();

        $this->vessels->each(function (Vessel $vessel, int $index) use ($types, $ports, $nationalOwners, $foreignOwners, $localAgent, $people) {
            VesselRelationsSynchronizer::syncAll($vessel);

            $isPatrol = $vessel->category === VesselCategory::Patrol;
            $isNational = $vessel->flag === 'CI';
            $typeCode = match ($vessel->type) {
                VesselType::Trawler => 'TRW',
                VesselType::Seiner => 'PS',
                VesselType::Longliner => 'LL',
                VesselType::Canoe => 'CAN',
                VesselType::PatrolBoat => 'PAT',
                default => 'SUP',
            };

            $vessel->forceFill([
                'creation_origin' => $isPatrol ? SourceSystem::Neno : SourceSystem::ManualEntry,
                'fleet_segment' => match (true) {
                    $isPatrol => FleetSegment::PatrolVessel,
                    ! $isNational => FleetSegment::AuthorizedForeign,
                    $vessel->type === VesselType::Canoe => FleetSegment::Artisanal,
                    $vessel->type === VesselType::Trawler => FleetSegment::SemiIndustrial,
                    default => FleetSegment::Industrial,
                },
                'fleet_type' => FleetType::Fishing,
                'legal_status' => $isNational ? LegalStatus::Ivorian : LegalStatus::Foreign,
                'is_tuna_vessel' => in_array($vessel->type, [VesselType::Seiner, VesselType::Longliner], true),
                'vessel_type_id' => $types[$typeCode],
                'home_port_id' => $isNational ? $ports[$index % $ports->count()] : null,
                'construction_year' => 1988 + ($index * 7) % 35,
                'license_not_provided' => true,
            ])->saveQuietly();

            VesselFieldProvenance::insert(collect(['name', 'flag_country_id', 'vessel_type_id', 'fleet_segment'])->map(fn (string $field) => [
                'vessel_id' => $vessel->id, 'field' => $field, 'source' => ($isPatrol ? SourceSystem::Neno : SourceSystem::ManualEntry)->value, 'updated_at' => now(),
            ])->all());

            VesselSource::create([
                'vessel_id' => $vessel->id,
                'source' => $isPatrol ? SourceSystem::Neno : SourceSystem::ManualEntry,
                'external_id' => $isPatrol ? 'NENO-'.$vessel->registration_number : null,
                'last_import_at' => now()->subDays($index % 9),
                'matching_status' => MatchingStatus::New,
            ]);

            if (! $isPatrol) {
                $vessel->identifiers()->create(['type' => IdentifierType::Mmsi, 'value' => ($isNational ? '619' : '2'.str_pad((string) (10 + $index % 80), 2, '0', STR_PAD_LEFT)).str_pad((string) (100000 + $index * 37), 6, '0', STR_PAD_LEFT)]);
            }

            $owners = $isNational ? $nationalOwners : $foreignOwners;
            $owner = $isPatrol ? null : $owners[$index % $owners->count()];

            if ($owner) {
                VesselActor::create(['vessel_id' => $vessel->id, 'role' => ActorRole::Owner, 'legal_entity_id' => $owner->id, 'started_on' => today()->subYears(2 + $index % 5)]);
            }

            if (! $isNational && ! $isPatrol) {
                VesselActor::create(['vessel_id' => $vessel->id, 'role' => ActorRole::LocalAgent, 'legal_entity_id' => $localAgent->id, 'started_on' => today()->subYear()]);
                VesselActor::create(['vessel_id' => $vessel->id, 'role' => ActorRole::Captain, 'person_id' => $people[($index % 3) + 4]->id, 'started_on' => today()->subMonths(8)]);
            }

            if ($index % 11 === 0 && ! $isPatrol) {
                VesselHistory::create(['vessel_id' => $vessel->id, 'former_name' => $vessel->name.' II', 'changed_on' => today()->subYears(3)]);
            }
        });
    }

    private function seedExternalLists(): void
    {
        $iccat = Institution::where('name', 'like', 'ICCAT%')->first();
        $fao = Institution::where('name', 'like', 'FAO%')->first();
        $corep = Institution::where('name', 'like', 'COREP%')->first();

        $lists = [
            ExternalList::create(['institution_id' => $fao->id, 'type' => ExternalListType::GlobalRecord, 'label' => 'Fichier mondial des navires de pêche']),
            ExternalList::create(['institution_id' => $iccat->id, 'type' => ExternalListType::Rfmo, 'label' => 'Registre ICCAT des navires autorisés']),
            ExternalList::create(['institution_id' => $corep->id, 'type' => ExternalListType::Inn, 'label' => 'Liste INN du COREP']),
        ];

        $foreign = $this->vessels->filter(fn (Vessel $vessel) => $vessel->flag !== 'CI' && $vessel->category !== VesselCategory::Patrol)->values();

        foreach ($foreign->take(12) as $vessel) {
            $vessel->externalLists()->attach($lists[0]->id, ['listed_from' => today()->subYears(4)]);
        }

        foreach ($foreign->filter(fn (Vessel $vessel) => $vessel->is_tuna_vessel)->take(6) as $vessel) {
            $vessel->externalLists()->attach($lists[1]->id, ['listed_from' => today()->subYears(2), 'listed_until' => today()->addYear()]);
        }

        if ($foreign->isNotEmpty()) {
            $foreign->last()->externalLists()->attach($lists[2]->id, ['listed_from' => today()->subMonths(5)]);
        }
    }

    private function seedSourcesAndDuplicates(): void
    {
        $lead = User::where('email', 'teamlead@example.com')->first();
        $actions = app(ActionRunner::class);

        $foreign = $this->vessels->first(fn (Vessel $vessel) => $vessel->flag !== 'CI' && $vessel->imo_number);
        $national = $this->vessels->first(fn (Vessel $vessel) => $vessel->flag === 'CI' && $vessel->category !== VesselCategory::Patrol && $vessel->imo_number);
        $namesake = $this->vessels->first(fn (Vessel $vessel) => $vessel->flag === 'CI' && $vessel->category !== VesselCategory::Patrol && $vessel->id !== $national->id);

        $records = [
            ['source' => 'themis', 'external_id' => 'THEMIS-'.$foreign->id, 'name' => $foreign->name, 'flag' => $foreign->flag, 'identifiers' => ['imo' => $foreign->imo_number], 'attributes' => ['length_m' => 54.2]],
            ['source' => 'e_inspection', 'external_id' => 'EINSP-'.$foreign->id, 'name' => $foreign->name, 'flag' => $foreign->flag, 'identifiers' => ['imo' => $foreign->imo_number, 'ircs' => $foreign->call_sign], 'attributes' => []],
            ['source' => 'e_inspection', 'external_id' => 'EINSP-N-'.$namesake->id, 'name' => mb_strtolower($namesake->name), 'flag' => 'CI', 'identifiers' => [], 'attributes' => []],
            ['source' => 'neno', 'external_id' => 'NENO-DUP-1', 'name' => $national->name, 'flag' => 'CI', 'identifiers' => ['imo' => $national->imo_number, 'mmsi' => '619999001'], 'attributes' => []],
            ['source' => 'dpep', 'external_id' => 'DPEP-2026-0412', 'name' => 'ATLANTIC STAR', 'flag' => 'LR', 'identifiers' => ['imo' => '9876543', 'ircs' => 'A8XY2'], 'attributes' => ['fleet_segment' => 'known_unauthorized']],
        ];

        foreach ($records as $record) {
            $actions->run(IngestVesselSource::class, $lead, $record);
        }
    }
}
