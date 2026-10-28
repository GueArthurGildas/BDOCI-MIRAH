<?php

namespace App\Support\Core;

use App\Models\ReferenceList;
use App\Models\ReferenceValue;

class ReferenceLists
{
    /** @return array<string, string> */
    public function options(string $listCode): array
    {
        return ReferenceValue::query()
            ->active()
            ->whereHas('list', fn ($query) => $query->where('code', $listCode))
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (ReferenceValue $value) => [$value->code => $value->translatedLabel()])
            ->all();
    }

    /** @param array<string, array<string, string>> $values code => [locale => label] */
    public function define(string $listCode, array $values): ReferenceList
    {
        $list = ReferenceList::firstOrCreate(['code' => $listCode]);

        $order = 0;
        foreach ($values as $code => $label) {
            ReferenceValue::updateOrCreate(
                ['reference_list_id' => $list->id, 'code' => $code],
                ['label' => $label, 'sort_order' => $order++],
            );
        }

        return $list;
    }
}
