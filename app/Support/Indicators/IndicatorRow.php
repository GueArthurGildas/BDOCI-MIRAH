<?php

namespace App\Support\Indicators;

use App\Models\Vessel;
use Closure;
use Illuminate\Support\Carbon;

final readonly class IndicatorRow
{
    public function __construct(
        public Vessel $vessel,
        public ?Carbon $date,
        private string|Closure|null $detail = null,
        public ?string $followUp = null,
        public ?string $module = null,
    ) {}

    public function detail(): ?string
    {
        return $this->detail instanceof Closure ? ($this->detail)() : $this->detail;
    }
}
