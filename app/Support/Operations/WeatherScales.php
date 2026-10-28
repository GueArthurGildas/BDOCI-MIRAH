<?php

namespace App\Support\Operations;

use App\Models\ReferenceList;
use App\Models\ReferenceListValue;

final class WeatherScales
{
    public const VISIBILITY = 'weather_visibility';

    public const CLOUD_COVER = 'weather_cloud_cover';

    public const SEA_STATE = 'weather_sea_state';

    private const SCALES = [
        self::VISIBILITY => ['Visibilité', ['good' => 'Bonne', 'moderate' => 'Moyenne', 'poor' => 'Mauvaise', 'fog' => 'Brouillard']],
        self::CLOUD_COVER => ['Nébulosité', ['clear' => 'Dégagé', 'partly_cloudy' => 'Partiellement nuageux', 'overcast' => 'Couvert']],
        self::SEA_STATE => ['État de la mer', ['calm' => 'Calme', 'slight' => 'Peu agitée', 'moderate' => 'Agitée', 'rough' => 'Forte', 'very_rough' => 'Très forte']],
    ];

    public function ensure(): void
    {
        foreach (self::SCALES as $code => [$label, $values]) {
            $list = ReferenceList::firstOrCreate(['code' => $code], ['label' => $label]);

            if ($list->values()->doesntExist()) {
                $order = 0;

                foreach ($values as $valueCode => $valueLabel) {
                    ReferenceListValue::create(['reference_list_id' => $list->id, 'code' => $valueCode, 'label' => $valueLabel, 'sort_order' => $order += 10]);
                }
            }
        }
    }

    /** @return array<string, string> */
    public function options(string $scale): array
    {
        $this->ensure();

        return ReferenceList::where('code', $scale)->firstOrFail()->values()->where('is_active', true)->pluck('label', 'code')->all();
    }
}
