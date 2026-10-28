<?php

namespace App\Support\Vms;

use App\Enums\ZoneType;
use App\Models\ReferenceList;
use App\Models\ReferenceListValue;
use App\Models\Zone;
use Illuminate\Support\Str;

final class LocalityResolver
{
    public const LIST_CODE = 'counting_localities';

    public function ensure(): ReferenceList
    {
        $list = ReferenceList::firstOrCreate(['code' => self::LIST_CODE], ['label' => 'Localités à compter']);

        if ($list->values()->doesntExist()) {
            foreach (config('surveillance.vms.counting_localities') as $index => $name) {
                ReferenceListValue::create(['reference_list_id' => $list->id, 'code' => Str::slug($name), 'label' => $name, 'sort_order' => ($index + 1) * 10]);
            }
        }

        return $list;
    }

    public function idFor(?int $zoneId): ?int
    {
        $zone = $zoneId === null ? null : Zone::find($zoneId);

        if ($zone === null || $zone->type !== ZoneType::PortArea) {
            return null;
        }

        return $this->ensure()->values()->where('is_active', true)->where('code', Str::slug($zone->name))->value('id');
    }
}
