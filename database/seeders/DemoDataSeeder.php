<?php

namespace Database\Seeders;

use App\Enums\CatchSource;
use App\Enums\InfractionStatus;
use App\Enums\InspectionResult;
use App\Enums\InspectionType;
use App\Enums\LicenseSegment;
use App\Enums\MissionStatus;
use App\Enums\MissionType;
use App\Enums\MovementDirection;
use App\Enums\ObservationLevel;
use App\Enums\ObservationStatus;
use App\Enums\TrackingSystem;
use App\Enums\VesselStatus;
use App\Enums\VesselType;
use App\Enums\ZoneType;
use App\Models\CatchDeclaration;
use App\Models\Infraction;
use App\Models\InfractionType;
use App\Models\Inspection;
use App\Models\License;
use App\Models\Mission;
use App\Models\Movement;
use App\Models\Observation;
use App\Models\Port;
use App\Models\Position;
use App\Models\Species;
use App\Models\Team;
use App\Models\TrackingDevice;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Authorization\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DemoDataSeeder extends Seeder
{
    private const RANDOM_SEED = 2026;

    private const NATIONAL_FISHING_VESSELS = 60;

    private const FOREIGN_FISHING_VESSELS = 30;

    private const PATROL_BOATS = 6;

    private const VESSELS_IN_PORT_AREAS = ['Abidjan' => 15, 'San Pedro' => 9, 'Sassandra' => 5, 'Tabou' => 3];

    private const VESSELS_IN_TERRITORIAL_SEA = 10;

    private const SILENT_BEACONS = 8;

    private const TRACK_POINTS = 12;

    private const TRACK_INTERVAL_HOURS = 2;

    private const NATIONAL_NAMES = [
        'AKWABA', 'ATLANTIQUE', 'BANDAMA', 'CAVALLY', 'COMOÉ', 'EBRIÉ', 'GOLFE DE GUINÉE', 'LAGUNE',
        'MARAHOUÉ', 'SASSANDRA', 'TIAGBA', 'ASSINIE', 'LAHOU', 'FRESCO', 'ABOISSO', 'BIA',
    ];

    private const FOREIGN_FLEETS = [
        'ES' => ['ALBACORA', 'TXORI', 'IZURDIA'],
        'FR' => ['TALENDUIC', 'GLENAN', 'TREVIGNON'],
        'CN' => ['HAI FENG', 'LU RONG YU', 'FU YUAN YU'],
        'KR' => ['DONG WON', 'SAJO'],
        'GH' => ['MAWULI', 'AFKO'],
        'SN' => ['SOPERKA', 'DIOR'],
    ];

    private Collection $zones;

    public function run(): void
    {
        fake()->seed(self::RANDOM_SEED);
        $this->zones = Zone::all()->keyBy('name');

        $agents = $this->seedAgents();
        $fishingVessels = $this->seedFishingVessels();
        $patrolBoats = $this->seedPatrolBoats();

        $this->seedLicenses($fishingVessels->where('type', '!=', VesselType::Canoe));
        $this->seedTrackingAndPositions($fishingVessels->where('type', '!=', VesselType::Canoe), $patrolBoats);
        $this->seedMovements($fishingVessels, $agents);
        $this->seedObservations($fishingVessels, $agents);
        $this->seedInspectionsAndInfractions($fishingVessels);
        $this->seedCatchDeclarations($fishingVessels->where('type', '!=', VesselType::Canoe));
        $this->seedMissions($patrolBoats, $fishingVessels, $agents);
    }

    private function seedAgents(): Collection
    {
        return Team::all()->flatMap(fn (Team $team) => User::factory(2)
            ->create(['team_id' => $team->id])
            ->each(fn (User $user) => $user->assignRole(Role::Agent->value)));
    }

    private function seedFishingVessels(): Collection
    {
        $national = collect(range(1, self::NATIONAL_FISHING_VESSELS))->map(fn (int $index) => Vessel::factory()->create([
            'name' => fake()->randomElement(self::NATIONAL_NAMES).' '.$index,
            'registration_number' => sprintf('AN-%04d', 1000 + $index),
            'flag' => 'CI',
            'type' => fake()->randomElement([VesselType::Trawler, VesselType::Seiner, VesselType::Canoe, VesselType::Canoe, VesselType::Longliner]),
            'status' => fake()->randomElement([VesselStatus::Active, VesselStatus::Active, VesselStatus::Active, VesselStatus::Suspended]),
        ]));

        $foreign = collect(range(1, self::FOREIGN_FISHING_VESSELS))->map(function (int $index) {
            $flag = fake()->randomElement(array_keys(self::FOREIGN_FLEETS));

            return Vessel::factory()->create([
                'name' => fake()->randomElement(self::FOREIGN_FLEETS[$flag]).' '.fake()->numberBetween(1, 99),
                'registration_number' => sprintf('%s-%05d', $flag, fake()->unique()->numberBetween(10000, 99999)),
                'flag' => $flag,
                'type' => fake()->randomElement([VesselType::Seiner, VesselType::Longliner, VesselType::Trawler]),
                'status' => VesselStatus::Active,
            ]);
        });

        return $national->concat($foreign);
    }

    private function seedPatrolBoats(): Collection
    {
        return collect(range(1, self::PATROL_BOATS))->map(fn (int $index) => Vessel::factory()->patrol()->create([
            'name' => sprintf('VEDETTE CSP %02d', $index),
            'registration_number' => sprintf('CSP-V%02d', $index),
            'owner' => 'Centre de Surveillance des Pêches',
            'length_meters' => fake()->randomFloat(2, 9, 18),
        ]));
    }

    private function seedLicenses(Collection $vessels): void
    {
        $vessels->values()->each(function (Vessel $vessel, int $index) {
            $factory = License::factory()->for($vessel)->state([
                'segment' => $vessel->flag === 'CI' ? LicenseSegment::Artisanal : LicenseSegment::Industrial,
            ]);

            match (true) {
                $index < 6 => $factory->expiringIn(fake()->numberBetween(3, 28))->create(),
                $index < 11 => $factory->expired()->create(),
                $index < 13 => $factory->suspended()->create(),
                default => $factory->create(),
            };

            if ($index % 2 === 0) {
                License::factory()->for($vessel)->expired()->create(['segment' => LicenseSegment::Industrial]);
            }
        });
    }

    private function seedTrackingAndPositions(Collection $fishingVessels, Collection $patrolBoats): void
    {
        $destinations = $this->positionDestinations($fishingVessels->count());
        $firstSilentIndex = $fishingVessels->count() - self::SILENT_BEACONS;

        $fishingVessels->values()->each(function (Vessel $vessel, int $index) use ($destinations, $firstSilentIndex) {
            if ($index % 6 === 0) {
                TrackingDevice::factory()->for($vessel)->create([
                    'installed_at' => today()->subYears(4),
                    'removed_at' => today()->subYears(2),
                ]);
            }

            $device = TrackingDevice::factory()->for($vessel)->create();
            $isSilent = $index >= $firstSilentIndex;
            $lastEmission = $isSilent ? now()->subHours(fake()->numberBetween(5, 30)) : now()->subMinutes(fake()->numberBetween(5, 50));

            $this->seedTrack($vessel, $device, $destinations[$index], $lastEmission);
        });

        $patrolBoats->each(function (Vessel $boat) {
            $device = TrackingDevice::factory()->for($boat)->create([
                'system' => TrackingSystem::Ais,
                'identifier' => fake()->unique()->numerify('6190#####'),
            ]);

            $this->seedTrack($boat, $device, $this->pointInPortArea('Abidjan'), now()->subMinutes(fake()->numberBetween(5, 30)));
        });
    }

    private function positionDestinations(int $vesselCount): array
    {
        $destinations = [];

        foreach (self::VESSELS_IN_PORT_AREAS as $locality => $count) {
            foreach (range(1, $count) as $vessel) {
                $destinations[] = $this->pointInPortArea($locality);
            }
        }

        foreach (range(1, self::VESSELS_IN_TERRITORIAL_SEA) as $vessel) {
            $destinations[] = ['zone' => $this->zones['Mer territoriale'], 'latitude' => fake()->randomFloat(4, 4.55, 4.95), 'longitude' => fake()->randomFloat(4, -6.5, -3.5)];
        }

        while (count($destinations) < $vesselCount) {
            $destinations[] = ['zone' => $this->zones['Zone économique exclusive'], 'latitude' => fake()->randomFloat(4, 2.2, 4.2), 'longitude' => fake()->randomFloat(4, -7.3, -3.0)];
        }

        return fake()->shuffle($destinations);
    }

    private function pointInPortArea(string $locality): array
    {
        $port = Port::where('locality', $locality)->firstOrFail();
        $spread = ReferenceDataSeeder::PORT_AREA_RADIUS_DEGREES * 0.7;

        return [
            'zone' => $this->zones[$locality],
            'latitude' => $port->latitude + fake()->randomFloat(4, -$spread, 0),
            'longitude' => $port->longitude + fake()->randomFloat(4, -$spread, $spread),
        ];
    }

    private function seedTrack(Vessel $vessel, TrackingDevice $device, array $destination, Carbon $lastEmission): void
    {
        $isMoored = $destination['zone']->type === ZoneType::PortArea;
        $step = $isMoored ? 0.005 : 0.06;
        $latitude = $destination['latitude'];
        $longitude = $destination['longitude'];
        $points = [];

        foreach (range(0, self::TRACK_POINTS - 1) as $hoursBack) {
            $points[] = [
                'vessel_id' => $vessel->id,
                'tracking_device_id' => $device->id,
                'zone_id' => $destination['zone']->id,
                'system' => $device->system->value,
                'recorded_at' => $lastEmission->copy()->subHours($hoursBack * self::TRACK_INTERVAL_HOURS),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed_knots' => $isMoored ? 0 : fake()->randomFloat(2, 2, 11),
                'course' => fake()->numberBetween(0, 359),
            ];

            $latitude -= fake()->randomFloat(4, 0, $step);
            $longitude += fake()->randomFloat(4, -$step, $step);
        }

        Position::insert($points);
    }

    private function seedMovements(Collection $vessels, Collection $agents): void
    {
        $ports = Port::all();

        foreach (range(1, 80) as $movement) {
            Movement::factory()->create([
                'vessel_id' => $vessels->random()->id,
                'port_id' => $ports->random()->id,
                'recorded_by' => $agents->random()->id,
                'direction' => fake()->randomElement(MovementDirection::cases()),
                'occurred_at' => now()->subMinutes(fake()->numberBetween(30, 60 * 24 * 15)),
                'reason' => fake()->randomElement(['Marée commerciale', 'Débarquement', 'Avitaillement', 'Réparation', 'Escale technique']),
                'notes' => null,
            ]);
        }
    }

    private function seedObservations(Collection $vessels, Collection $agents): void
    {
        $situations = [
            [ObservationLevel::Critical, 'Rupture d\'émission VMS depuis plus de 6 heures. Dernière position connue en ZEE.'],
            [ObservationLevel::Warning, 'Vitesse incohérente avec l\'activité déclarée (moins de 2 nœuds en zone de pêche).'],
            [ObservationLevel::Warning, 'Trajectoire en zigzag à proximité de la mer territoriale.'],
            [ObservationLevel::Critical, 'Navire signalé dans l\'aire marine protégée des îles Ehotilé.'],
            [ObservationLevel::Info, 'Retour au port sans déclaration d\'entrée préalable.'],
            [ObservationLevel::Info, 'Licence arrivant à échéance, armateur à relancer.'],
        ];

        foreach (range(1, 30) as $index) {
            [$level, $description] = fake()->randomElement($situations);
            $author = $agents->random();
            $status = fake()->randomElement(ObservationStatus::cases());

            Observation::factory()->create([
                'vessel_id' => $vessels->random()->id,
                'team_id' => $author->team_id,
                'author_id' => $author->id,
                'zone_id' => $this->zones->random()->id,
                'observed_at' => now()->subMinutes(fake()->numberBetween(20, 60 * 24 * 10)),
                'description' => $description,
                'level' => $level,
                'status' => $status,
                'follow_up' => $status === ObservationStatus::Open ? null : 'Relais transmis à l\'équipe suivante et au responsable du CSP.',
                'closed_at' => $status === ObservationStatus::Closed ? now()->subHours(fake()->numberBetween(1, 48)) : null,
            ]);
        }
    }

    private function seedInspectionsAndInfractions(Collection $vessels): void
    {
        $ports = Port::all();
        $infractionTypes = InfractionType::all();

        foreach (range(1, 50) as $index) {
            $result = fake()->randomElement([InspectionResult::Compliant, InspectionResult::Compliant, InspectionResult::MinorIssues, InspectionResult::NonCompliant]);
            $type = fake()->randomElement(InspectionType::cases());

            $inspection = Inspection::factory()->create([
                'vessel_id' => $vessels->random()->id,
                'port_id' => $type === InspectionType::AtSea ? null : $ports->random()->id,
                'external_reference' => sprintf('EI-2026-%05d', $index),
                'type' => $type,
                'inspected_at' => now()->subDays(fake()->numberBetween(1, 300)),
                'inspectors' => fake()->name().', '.fake()->name(),
                'result' => $result,
                'findings' => $result === InspectionResult::Compliant ? 'Documents de bord et engins conformes.' : 'Écarts constatés lors du contrôle des engins et des documents de bord.',
            ]);

            if ($result === InspectionResult::NonCompliant) {
                Infraction::factory()->create([
                    'reference' => sprintf('PV-2026-%03d', $index),
                    'vessel_id' => $inspection->vessel_id,
                    'inspection_id' => $inspection->id,
                    'infraction_type_id' => $infractionTypes->random()->id,
                    'detected_at' => $inspection->inspected_at,
                    'facts' => 'Manquement constaté lors de l\'inspection '.$inspection->external_reference.'.',
                    'status' => fake()->randomElement(InfractionStatus::cases()),
                ]);
            }
        }
    }

    private function seedCatchDeclarations(Collection $vessels): void
    {
        $species = Species::all();
        $fishingZones = $this->zones->whereIn('type', [ZoneType::ExclusiveEconomicZone, ZoneType::TerritorialSea]);

        foreach (range(1, 150) as $declaration) {
            $periodStart = today()->subDays(fake()->numberBetween(7, 180));

            CatchDeclaration::factory()->create([
                'vessel_id' => $vessels->random()->id,
                'species_id' => $species->random()->id,
                'zone_id' => $fishingZones->random()->id,
                'period_start' => $periodStart,
                'period_end' => $periodStart->copy()->addDays(fake()->numberBetween(3, 15)),
                'quantity_kg' => fake()->randomFloat(2, 200, 45000),
                'source' => fake()->randomElement(CatchSource::cases()),
            ]);
        }
    }

    private function seedMissions(Collection $patrolBoats, Collection $fishingVessels, Collection $agents): void
    {
        foreach (range(1, 12) as $index) {
            $startsAt = now()->subDays(fake()->numberBetween(-5, 120));
            $status = match (true) {
                $startsAt->isFuture() => MissionStatus::Planned,
                $startsAt->gt(now()->subDays(2)) => MissionStatus::InProgress,
                default => fake()->randomElement([MissionStatus::Completed, MissionStatus::Completed, MissionStatus::Cancelled]),
            };

            $mission = Mission::factory()->create([
                'reference' => sprintf('MIS-2026-%03d', $index),
                'zone_id' => $this->zones->random()->id,
                'type' => fake()->randomElement(MissionType::cases()),
                'objective' => fake()->randomElement([
                    'Patrouille de contrôle des chalutiers en mer territoriale.',
                    'Contrôle des débarquements au port de pêche.',
                    'Surveillance de l\'aire marine protégée.',
                    'Opération conjointe avec la Marine nationale en ZEE.',
                ]),
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addDays(fake()->numberBetween(1, 4)),
                'status' => $status,
                'results' => $status === MissionStatus::Completed ? fake()->numberBetween(3, 15).' navires contrôlés.' : null,
            ]);

            $mission->vessels()->attach([
                $patrolBoats->random()->id,
                ...$fishingVessels->random(fake()->numberBetween(2, 5))->pluck('id'),
            ]);
            $mission->agents()->attach($agents->random(fake()->numberBetween(2, 4))->pluck('id'));
        }
    }
}
