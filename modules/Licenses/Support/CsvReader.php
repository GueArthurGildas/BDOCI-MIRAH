<?php

namespace Modules\Licenses\Support;

use App\Models\Vessel;

class CsvReader
{
    public const COLUMNS = ['registration_number', 'number', 'segment', 'issued_on', 'expires_on', 'zones', 'gears', 'species', 'conditions'];

    private const LIST_SEPARATOR = '|';

    /** @return array<int, array{vessel_id: ?int, data: array<string, mixed>}> */
    public function read(string $content): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim(preg_replace('/^\xEF\xBB\xBF/', '', $content)));

        if ($lines === false || count($lines) < 2) {
            return [];
        }

        $delimiter = substr_count($lines[0], ';') >= substr_count($lines[0], ',') ? ';' : ',';
        $header = array_map(fn (string $name) => strtolower(trim($name)), str_getcsv($lines[0], $delimiter));

        return collect(array_slice($lines, 1))
            ->filter(fn (string $line) => trim($line) !== '')
            ->map(fn (string $line) => $this->row(array_combine($header, array_pad(array_slice(str_getcsv($line, $delimiter), 0, count($header)), count($header), ''))))
            ->values()
            ->all();
    }

    /** @param array<string, string> $row */
    private function row(array $row): array
    {
        $registration = trim($row['registration_number'] ?? '');

        return [
            'vessel_id' => $registration === '' ? null : Vessel::where('registration_number', $registration)->value('id'),
            'data' => [
                'registration_number' => $registration,
                'number' => trim($row['number'] ?? ''),
                'segment' => trim($row['segment'] ?? ''),
                'issued_on' => trim($row['issued_on'] ?? ''),
                'expires_on' => trim($row['expires_on'] ?? ''),
                'zones' => $this->list($row['zones'] ?? ''),
                'gears' => $this->list($row['gears'] ?? ''),
                'species' => $this->list($row['species'] ?? ''),
                'conditions' => $this->list($row['conditions'] ?? ''),
            ],
        ];
    }

    /** @return array<int, string> */
    private function list(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(self::LIST_SEPARATOR, $value))));
    }
}
