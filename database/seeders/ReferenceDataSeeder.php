<?php

namespace Database\Seeders;

use App\Enums\DataSourceKind;
use App\Enums\DataSourceStatus;
use App\Enums\ZoneType;
use App\Models\DataSource;
use App\Models\InfractionType;
use App\Models\Port;
use App\Models\Species;
use App\Models\Team;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public const PORT_AREA_RADIUS_DEGREES = 0.12;

    private const PORTS = [
        ['Port autonome d\'Abidjan', 'Abidjan', 5.2580, -4.0090, true],
        ['Port autonome de San Pedro', 'San Pedro', 4.7460, -6.6360, true],
        ['Port de Sassandra', 'Sassandra', 4.9510, -6.0850, true],
        ['Port de Tabou', 'Tabou', 4.4230, -7.3530, true],
        ['Débarcadère de Grand-Bassam', 'Grand-Bassam', 5.1960, -3.7380, false],
        ['Débarcadère de Jacqueville', 'Jacqueville', 5.2050, -4.4180, false],
    ];

    private const COASTLINE = [
        [-7.55, 4.35], [-7.35, 4.42], [-6.64, 4.74], [-6.08, 4.95], [-5.57, 5.08],
        [-5.02, 5.14], [-4.42, 5.20], [-4.01, 5.25], [-3.74, 5.19], [-3.28, 5.12], [-2.75, 5.10],
    ];

    private const TERRITORIAL_SEA_WIDTH_DEGREES = 0.2;

    private const SPECIES = [
        ['YFT', 'Albacore', 'Thunnus albacares'],
        ['SKJ', 'Listao', 'Katsuwonus pelamis'],
        ['BET', 'Thon obèse', 'Thunnus obesus'],
        ['SAA', 'Sardinelle ronde', 'Sardinella aurita'],
        ['SAE', 'Sardinelle plate', 'Sardinella maderensis'],
        ['BOA', 'Ethmalose', 'Ethmalosa fimbriata'],
        ['SWO', 'Espadon', 'Xiphias gladius'],
    ];

    private const INFRACTION_TYPES = [
        ['INF-01', 'Pêche sans licence valide', 'Loi n° 2016-554, art. 23'],
        ['INF-02', 'Utilisation d\'engin prohibé', 'Loi n° 2016-554, art. 41'],
        ['INF-03', 'Pêche en zone interdite', 'Loi n° 2016-554, art. 37'],
        ['INF-04', 'Non-émission de la balise VMS', 'Arrêté VMS, art. 6'],
        ['INF-05', 'Maillage non réglementaire', 'Loi n° 2016-554, art. 42'],
        ['INF-06', 'Transbordement non autorisé', 'Loi n° 2016-554, art. 48'],
        ['INF-07', 'Défaut de déclaration des captures', 'Loi n° 2016-554, art. 55'],
    ];

    private const TEAMS = ['Équipe A', 'Équipe B', 'Équipe C'];

    public function run(): void
    {
        $this->seedGeography();

        foreach (self::SPECIES as [$code, $name, $scientificName]) {
            Species::create(['fao_code' => $code, 'name' => $name, 'scientific_name' => $scientificName]);
        }

        foreach (self::INFRACTION_TYPES as [$code, $name, $legalBasis]) {
            InfractionType::create(['code' => $code, 'name' => $name, 'legal_basis' => $legalBasis]);
        }

        foreach (self::TEAMS as $team) {
            Team::create(['name' => $team]);
        }

        $this->seedDataSources();
    }

    private function seedGeography(): void
    {
        Zone::create([
            'name' => 'Zone économique exclusive',
            'type' => ZoneType::ExclusiveEconomicZone,
            'geometry' => $this->polygon([...self::COASTLINE, [-2.75, 1.50], [-7.55, 1.50]]),
        ]);

        Zone::create([
            'name' => 'Mer territoriale',
            'type' => ZoneType::TerritorialSea,
            'geometry' => $this->polygon([
                ...self::COASTLINE,
                ...array_reverse(array_map(
                    fn (array $point) => [$point[0], $point[1] - self::TERRITORIAL_SEA_WIDTH_DEGREES],
                    self::COASTLINE,
                )),
            ]),
        ]);

        Zone::create([
            'name' => 'Aire marine protégée des îles Ehotilé',
            'type' => ZoneType::Restricted,
            'geometry' => $this->square(5.12, -3.22, 0.06),
        ]);

        foreach (self::PORTS as [$name, $locality, $latitude, $longitude, $hasPortArea]) {
            $zone = $hasPortArea ? Zone::create([
                'name' => $locality,
                'type' => ZoneType::PortArea,
                'geometry' => $this->square($latitude, $longitude, self::PORT_AREA_RADIUS_DEGREES),
            ]) : null;

            Port::create([
                'name' => $name,
                'locality' => $locality,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'zone_id' => $zone?->id,
            ]);
        }
    }

    private function seedDataSources(): void
    {
        $sources = [
            ['vms-themis', 'VMS / THEMIS', DataSourceKind::Api, DataSourceStatus::Operational, now()->subMinutes(5), 0, 0],
            ['e-inspection', 'e-inspection', DataSourceKind::Database, DataSourceStatus::Operational, now()->subHour(), 0, 3],
            ['missions', 'Opérations / Missions', DataSourceKind::Api, DataSourceStatus::Degraded, now()->subHours(7), 2, 0],
            ['supervision-reports', 'Rapports et supervision', DataSourceKind::File, DataSourceStatus::Operational, now()->subDay(), 0, 1],
            ['foreign-vessels', 'Navires étrangers', DataSourceKind::Database, DataSourceStatus::Down, now()->subDays(4), 5, 12],
        ];

        foreach ($sources as [$key, $name, $kind, $status, $lastSyncedAt, $errorCount, $rejectedCount]) {
            DataSource::create([
                'key' => $key,
                'name' => $name,
                'kind' => $kind,
                'status' => $status,
                'last_synced_at' => $lastSyncedAt,
                'error_count' => $errorCount,
                'rejected_count' => $rejectedCount,
            ]);
        }
    }

    private function square(float $latitude, float $longitude, float $radius): array
    {
        return $this->polygon([
            [$longitude - $radius, $latitude - $radius],
            [$longitude + $radius, $latitude - $radius],
            [$longitude + $radius, $latitude + $radius],
            [$longitude - $radius, $latitude + $radius],
        ]);
    }

    private function polygon(array $points): array
    {
        return ['type' => 'Polygon', 'coordinates' => [[...$points, $points[0]]]];
    }
}
