<?php

namespace Modules\Licenses\Support;

use App\Enums\LicenseSegment;
use App\Models\Gear;
use App\Models\License;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\Zone;

class ImportExample
{
    private const ROWS = 3;

    private const SEPARATOR = ';';

    private const LIST_SEPARATOR = '|';

    public const FILENAME = 'exemple_import_licences.csv';

    /** @return array<int, array{field: string, required: bool, format: string, example: string}> */
    public function fields(): array
    {
        $example = $this->rows()[0] ?? array_fill_keys(CsvReader::COLUMNS, '');

        return collect(CsvReader::COLUMNS)->map(fn (string $field) => [
            'field' => $field,
            'required' => in_array($field, ['registration_number', 'number', 'expires_on'], true),
            'format' => __('licenses::imports.example.formats.'.$field),
            'example' => (string) $example[$field],
        ])->all();
    }

    public function csv(): string
    {
        $lines = [
            '# '.__('licenses::imports.example.intro'),
            ...collect(CsvReader::COLUMNS)->map(fn (string $field) => '# '.$field.' : '.(in_array($field, ['registration_number', 'number', 'expires_on'], true) ? __('licenses::imports.example.required') : __('licenses::imports.example.optional')).'. '.__('licenses::imports.example.formats.'.$field))->all(),
            implode(self::SEPARATOR, CsvReader::COLUMNS),
            ...collect($this->rows())->map(fn (array $row) => implode(self::SEPARATOR, $row))->all(),
        ];

        return "\xEF\xBB\xBF".implode("\r\n", $lines)."\r\n";
    }

    /** @return array<int, array<string, string>> */
    private function rows(): array
    {
        $zones = Zone::orderBy('name')->limit(2)->pluck('name')->implode(self::LIST_SEPARATOR);
        $gears = Gear::orderBy('code_fao')->limit(2)->pluck('code_fao')->implode(self::LIST_SEPARATOR);
        $species = Species::orderBy('fao_code')->limit(2)->pluck('fao_code')->implode(self::LIST_SEPARATOR);
        $segments = array_map(fn (LicenseSegment $segment) => $segment->value, LicenseSegment::cases());

        return Vessel::query()
            ->where('category', 'fishing')
            ->whereNotNull('registration_number')
            ->orderBy('registration_number')
            ->limit(self::ROWS)
            ->get()
            ->values()
            ->map(fn (Vessel $vessel, int $index) => [
                'registration_number' => $vessel->registration_number,
                'number' => $this->freeNumber($index + 1),
                'segment' => $segments[$index % count($segments)],
                'issued_on' => now()->startOfYear()->toDateString(),
                'expires_on' => now()->endOfYear()->toDateString(),
                'zones' => $zones,
                'gears' => $gears,
                'species' => $species,
                'conditions' => __('licenses::imports.example.condition'),
            ])
            ->all();
    }

    private function freeNumber(int $index): string
    {
        $prefix = 'LIC-EXEMPLE-'.now()->year.'-';

        do {
            $number = $prefix.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
            $index += self::ROWS;
        } while (License::where('number', $number)->exists());

        return $number;
    }
}
