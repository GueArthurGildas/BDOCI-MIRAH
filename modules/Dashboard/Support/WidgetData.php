<?php

namespace Modules\Dashboard\Support;

use Illuminate\Support\Collection;

final readonly class WidgetData
{
    /**
     * @param  array<string, mixed>|null  $chart
     * @param  array<int, array{label: string, value: int, color: string}>  $figures
     */
    public function __construct(
        public Widget $widget,
        public ?int $value = null,
        public ?array $chart = null,
        public array $figures = [],
        public ?Collection $records = null,
    ) {}

    public function total(): int
    {
        return array_sum(array_column($this->figures, 'value'));
    }
}
