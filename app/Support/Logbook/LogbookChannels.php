<?php

namespace App\Support\Logbook;

use App\Models\ReferenceList;
use App\Models\ReferenceListValue;

final class LogbookChannels
{
    public const LIST_CODE = 'logbook_channels';

    public const INTERNAL = 'internal';

    private const DEFAULTS = [
        'phone' => 'Téléphone',
        'email' => 'E-mail',
        'themis' => 'THEMIS',
        'radio' => 'Radio',
        'mail' => 'Courrier',
        'in_person' => 'En personne',
        self::INTERNAL => 'Interne (application)',
    ];

    public function ensure(): ReferenceList
    {
        $list = ReferenceList::firstOrCreate(['code' => self::LIST_CODE], ['label' => 'Canaux de la main courante']);

        if ($list->values()->doesntExist()) {
            $order = 0;

            foreach (self::DEFAULTS as $code => $label) {
                ReferenceListValue::create(['reference_list_id' => $list->id, 'code' => $code, 'label' => $label, 'sort_order' => $order += 10]);
            }
        }

        return $list;
    }

    public function idFor(string $code): int
    {
        return $this->ensure()->values()->where('code', $code)->firstOrFail()->id;
    }

    /** @return array<int, string> */
    public function options(): array
    {
        return $this->ensure()->values()->where('is_active', true)->pluck('label', 'id')->all();
    }
}
