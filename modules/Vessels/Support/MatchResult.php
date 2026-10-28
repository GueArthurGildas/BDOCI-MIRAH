<?php

namespace Modules\Vessels\Support;

use App\Enums\MatchingCriterion;
use App\Enums\MatchingStatus;
use App\Models\Vessel;

final class MatchResult
{
    public function __construct(
        public readonly MatchingStatus $status,
        public readonly ?Vessel $candidate = null,
        public readonly ?MatchingCriterion $criterion = null,
    ) {}
}
