<?php

namespace App\Support\Vessels;

use App\Enums\VesselStatusLight;

final readonly class StatusLightAssessment
{
    /** @param array<int, array{key: string, params: array<string, scalar>}> $reasons */
    public function __construct(public VesselStatusLight $level, public array $reasons) {}
}
