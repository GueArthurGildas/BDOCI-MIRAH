<?php

namespace Modules\Infractions\Support;

use App\Models\InfractionCase;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class CaseFolders
{
    public const FOLDERS = ['summary', 'upheld', 'evidence', 'seals', 'measures', 'seizures', 'follow_ups', 'actors'];

    /**
     * @return array<string, Collection<int, array{label: string, detail: ?string, date: ?CarbonInterface}>>
     */
    public static function for(InfractionCase $case): array
    {
        $case->loadMissing(['upheld.type', 'evidence.document', 'seals', 'measures', 'seizures', 'followUps', 'captain', 'shipowner']);

        return [
            'upheld' => $case->upheld->map(fn ($upheld) => self::row($upheld->type?->label, $upheld->type?->code, $upheld->upheld_at)),
            'evidence' => $case->evidence->map(fn ($evidence) => self::row($evidence->description, $evidence->document?->name, null)),
            'seals' => $case->seals->map(fn ($seal) => self::row($seal->number, null, $seal->placed_at)),
            'measures' => $case->measures->map(fn ($measure) => self::row(self::label($measure->type), null, $measure->decided_at)),
            'seizures' => $case->seizures->map(fn ($seizure) => self::row($seizure->item, self::label($seizure->decision), null)),
            'follow_ups' => $case->followUps->map(fn ($followUp) => self::row(self::label($followUp->type), $followUp->closed_at ? __('infractions::infractions.folders.closed_on', ['date' => $followUp->closed_at->isoFormat('L')]) : __('infractions::infractions.folders.ongoing'), $followUp->opened_at)),
            'actors' => collect([
                $case->captain ? self::row($case->captain->full_name, __('infractions::infractions.folders.captain'), null) : null,
                $case->shipowner ? self::row($case->shipowner->name, __('infractions::infractions.folders.shipowner'), null) : null,
            ])->filter()->values(),
        ];
    }

    /** @return array{label: string, detail: ?string, date: ?CarbonInterface} */
    private static function row(mixed $label, ?string $detail, mixed $date): array
    {
        return ['label' => (string) $label, 'detail' => $detail, 'date' => $date];
    }

    private static function label(mixed $value): string
    {
        return $value instanceof BackedEnum && method_exists($value, 'label') ? __($value->label()) : (string) ($value instanceof BackedEnum ? $value->value : $value);
    }
}
