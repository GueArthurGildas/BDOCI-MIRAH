<?php

namespace App\Support\Vms;

use Illuminate\Support\Carbon;

final class PositionSnapshot
{
    public function __construct(
        public readonly int $vesselId,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly ?float $speed,
        public readonly ?int $zoneId,
        public readonly ?Carbon $emittedAt,
        public readonly ?string $activity,
    ) {}
}
