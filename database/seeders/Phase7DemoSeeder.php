<?php

namespace Database\Seeders;

use App\Enums\DataSourceMode;
use App\Models\DataSource;
use App\Models\ExternalLink;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Role;
use App\Support\Vessels\VesselRelationsSynchronizer;
use Illuminate\Database\Seeder;
use Modules\Administration\Actions\SynchroniseConnector;

class Phase7DemoSeeder extends Seeder
{
    private const VESSELS = [
        ['AKWABA PECHE 1', 'CI', '9701201', 'TUAB1', 'AN-1201-AB', 'trawler'],
        ['SAN PEDRO STAR', 'CI', '9701202', 'TUSP2', 'AN-1202-SP', 'seiner'],
        ['LAGUNE II', 'CI', '9701203', 'TULG3', 'AN-1203-AB', 'longliner'],
        ['ALBACORA CINCO', 'ES', '9302312', 'EAAL5', 'ES-3-2312', 'seiner'],
        ['DONG WON 301', 'KR', '8815017', 'DTBX3', 'KR-301-DW', 'trawler'],
        ['FU YUAN YU 7', 'CN', '9208654', 'BXYZ7', 'CN-7-FYY', 'longliner'],
    ];

    private const MMSI = [
        'ALBACORA CINCO' => '224301230',
        'FU YUAN YU 7' => '412345670',
        'AKWABA PECHE 1' => '619001201',
    ];

    private const EXTERNAL_LINKS = [
        'themis' => ['https://themis.example.org/vessels/{id}', false],
        'neno' => ['https://neno.example.org/vessel?ref={id}', false],
        'e_inspection' => [null, true],
    ];

    public function run(ActionRunner $actions): void
    {
        $this->seedVessels();
        $this->seedExternalLinks();

        $admin = User::role(Role::Admin->value)->firstOrFail();

        foreach (['e-inspection', 'dpep-mrep', 'vms-themis', 'neno'] as $code) {
            $source = DataSource::where('code', $code)->firstOrFail();
            $source->update(['mode' => DataSourceMode::Api]);
            $actions->run(SynchroniseConnector::class, $admin, ['data_source_id' => $source->id]);
        }

        DataSource::where('code', 'dpep-mrep')->update(['mode' => DataSourceMode::DirectRead]);
    }

    private function seedVessels(): void
    {
        foreach (self::VESSELS as [$name, $flag, $imo, $ircs, $registration, $type]) {
            $vessel = Vessel::factory()->create([
                'name' => $name,
                'flag' => $flag,
                'imo_number' => $imo,
                'call_sign' => $ircs,
                'registration_number' => $registration,
                'type' => $type,
            ]);

            VesselRelationsSynchronizer::syncAll($vessel);

            if (isset(self::MMSI[$name])) {
                $vessel->identifiers()->create(['type' => 'mmsi', 'value' => self::MMSI[$name]]);
            }
        }
    }

    private function seedExternalLinks(): void
    {
        foreach (self::EXTERNAL_LINKS as $source => [$url, $copyFallback]) {
            ExternalLink::updateOrCreate(['source' => $source], ['model_url' => $url, 'is_copy_fallback' => $copyFallback]);
        }
    }
}
