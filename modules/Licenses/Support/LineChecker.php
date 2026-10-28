<?php

namespace Modules\Licenses\Support;

use App\Enums\LicenseSegment;
use App\Models\Gear;
use App\Models\License;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\Zone;
use Illuminate\Support\Carbon;
use Throwable;

class LineChecker
{
    public const FIELDS = ['number', 'segment', 'issued_on', 'expires_on', 'zones', 'gears', 'species', 'conditions'];

    /** @param array<string, mixed> $data */
    public function rejection(array $data, ?int $vesselId, array $numbersInBatch = []): ?string
    {
        $number = trim((string) ($data['number'] ?? ''));

        return match (true) {
            $vesselId === null || ! Vessel::whereKey($vesselId)->exists() => 'unknown_vessel',
            $number === '' => 'missing_number',
            License::where('number', $number)->exists() || in_array($number, $numbersInBatch, true) => 'duplicate_number',
            filled($data['segment'] ?? null) && LicenseSegment::tryFrom($data['segment']) === null => 'invalid_segment',
            $this->datesAreInvalid($data) => 'invalid_dates',
            $this->unknown($data['zones'] ?? [], fn (array $names) => Zone::whereIn('name', $names)->pluck('name')->all()) => 'unknown_zone',
            $this->unknown($data['gears'] ?? [], fn (array $codes) => Gear::whereIn('code_fao', $codes)->pluck('code_fao')->all()) => 'unknown_gear',
            $this->unknown($data['species'] ?? [], fn (array $codes) => Species::whereIn('fao_code', $codes)->pluck('fao_code')->all()) => 'unknown_species',
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function licenseInput(array $data, int $vesselId): array
    {
        return [
            'vessel_id' => $vesselId,
            'number' => trim($data['number']),
            'segment' => filled($data['segment'] ?? null) ? $data['segment'] : null,
            'issued_on' => filled($data['issued_on'] ?? null) ? $data['issued_on'] : null,
            'expires_on' => $data['expires_on'],
            'zone_ids' => Zone::whereIn('name', $data['zones'] ?? [])->pluck('id')->all(),
            'gear_ids' => Gear::whereIn('code_fao', $data['gears'] ?? [])->pluck('id')->all(),
            'species_ids' => Species::whereIn('fao_code', $data['species'] ?? [])->pluck('id')->all(),
            'conditions' => $data['conditions'] ?? [],
        ];
    }

    /** @param array<string, mixed> $data */
    private function datesAreInvalid(array $data): bool
    {
        try {
            $expiresOn = Carbon::createFromFormat('Y-m-d', (string) ($data['expires_on'] ?? ''));
            $issuedOn = filled($data['issued_on'] ?? null) ? Carbon::createFromFormat('Y-m-d', $data['issued_on']) : null;
        } catch (Throwable) {
            return true;
        }

        return $expiresOn === false || $issuedOn === false || ($issuedOn !== null && $issuedOn->gt($expiresOn));
    }

    private function unknown(array $values, callable $known): bool
    {
        $values = array_values(array_filter($values));

        return $values !== [] && count(array_diff($values, $known($values))) > 0;
    }
}
