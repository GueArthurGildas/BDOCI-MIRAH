<?php

namespace App\Support\Infractions;

use App\Models\InfractionCase;
use Illuminate\Support\Facades\DB;

final readonly class RepeatOffense
{
    public function __construct(
        public int $vesselCases,
        public ?int $captainCases,
        public ?int $shipownerCases,
        public int $combinedCases,
        public int $threshold,
    ) {}

    public static function threshold(): int
    {
        return (int) config('surveillance.infractions.repeat_threshold');
    }

    public static function for(InfractionCase $case): self
    {
        $combined = DB::table('v_repeat_offenses')
            ->where('vessel_id', $case->vessel_id)
            ->when($case->captain_id, fn ($query, $id) => $query->where('captain_id', $id), fn ($query) => $query->whereNull('captain_id'))
            ->when($case->shipowner_id, fn ($query, $id) => $query->where('shipowner_id', $id), fn ($query) => $query->whereNull('shipowner_id'))
            ->value('cases_count');

        return new self(
            vesselCases: InfractionCase::where('vessel_id', $case->vessel_id)->count(),
            captainCases: $case->captain_id ? InfractionCase::where('captain_id', $case->captain_id)->count() : null,
            shipownerCases: $case->shipowner_id ? InfractionCase::where('shipowner_id', $case->shipowner_id)->count() : null,
            combinedCases: (int) $combined,
            threshold: self::threshold(),
        );
    }

    public function isVesselRepeat(): bool
    {
        return $this->vesselCases >= $this->threshold;
    }

    public function isCaptainRepeat(): bool
    {
        return $this->captainCases !== null && $this->captainCases >= $this->threshold;
    }

    public function isShipownerRepeat(): bool
    {
        return $this->shipownerCases !== null && $this->shipownerCases >= $this->threshold;
    }

    public function isRepeat(): bool
    {
        return $this->isVesselRepeat() || $this->isCaptainRepeat() || $this->isShipownerRepeat();
    }
}
