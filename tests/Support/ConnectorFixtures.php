<?php

namespace Tests\Support;

use App\Models\Gear;
use App\Models\Port;
use App\Models\Presentation;
use App\Models\Species;
use App\Models\Vessel;

final class ConnectorFixtures
{
    public static function referentials(): void
    {
        foreach (['YFT', 'SKJ', 'BET', 'SAA'] as $code) {
            Species::factory()->create(['fao_code' => $code]);
        }

        foreach (['PS', 'OTB', 'GNS'] as $code) {
            Gear::factory()->create(['code_fao' => $code]);
        }

        foreach (['WHL', 'GUT'] as $code) {
            Presentation::factory()->create(['code' => $code]);
        }

        Port::factory()->create(['name' => 'Port autonome d\'Abidjan', 'locality' => 'Abidjan']);
        Port::factory()->create(['name' => 'Port autonome de San Pedro', 'locality' => 'San Pedro']);
    }

    public static function vessels(): void
    {
        foreach ([['ALBACORA CINCO', '9302312', null], ['DONG WON 301', null, 'DTBX3'], ['AKWABA PECHE 1', '9701201', null], ['SAN PEDRO STAR', '9701202', null], ['LAGUNE II', '9701203', null]] as [$name, $imo, $ircs]) {
            Vessel::factory()->create(['name' => $name, 'imo_number' => $imo ?? fake()->unique()->numerify('IMO#######'), 'call_sign' => $ircs ?? fake()->unique()->bothify('TU##??')]);
        }
    }
}
