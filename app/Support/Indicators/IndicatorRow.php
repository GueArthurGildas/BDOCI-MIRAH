<?php

namespace Modules\Indicators\Support;

use App\Models\Vessel;
use Illuminate\Support\Carbon;

final readonly class IndicatorRow
{
    public function __construct(
        public Vessel $vessel,
        public ?Carbon $date,
        public ?string $detail = null,
        public ?string $followUp = null,
        public ?string $module = null,
    ) {}
}
