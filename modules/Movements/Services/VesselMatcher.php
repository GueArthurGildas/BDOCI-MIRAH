<?php

namespace Modules\Movements\Services;

use App\Models\Vessel;

class VesselMatcher
{
    /** @param array<string, mixed> $declared */
    public function find(array $declared): ?Vessel
    {
        return $this->byColumn('imo_number', $declared['declared_imo'] ?? null)
            ?? $this->byColumn('call_sign', $declared['declared_ircs'] ?? null)
            ?? $this->byName($declared['declared_name'] ?? null);
    }

    private function byColumn(string $column, ?string $value): ?Vessel
    {
        if (blank($value)) {
            return null;
        }

        return Vessel::whereRaw("upper({$column}) = ?", [mb_strtoupper(trim($value))])->first();
    }

    private function byName(?string $name): ?Vessel
    {
        if (blank($name)) {
            return null;
        }

        return Vessel::whereRaw('upper(name) = ?', [mb_strtoupper(trim($name))])->first();
    }
}
