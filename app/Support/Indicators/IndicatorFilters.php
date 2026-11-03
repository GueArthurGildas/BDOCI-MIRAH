<?php

namespace Modules\Indicators\Support;

use App\Enums\FleetSegment;
use App\Enums\VesselStatusLight;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

final readonly class IndicatorFilters
{
    public function __construct(
        public ?Carbon $from = null,
        public ?Carbon $to = null,
        public ?int $zoneId = null,
        public ?string $flag = null,
        public ?string $segment = null,
        public ?string $status = null,
        public ?string $search = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'flag' => ['nullable', 'string', 'size:2'],
            'segment' => ['nullable', Rule::enum(FleetSegment::class)],
            'status' => ['nullable', Rule::enum(VesselStatusLight::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return new self(
            from: isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : null,
            to: isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : null,
            zoneId: isset($data['zone_id']) ? (int) $data['zone_id'] : null,
            flag: $data['flag'] ?? null,
            segment: $data['segment'] ?? null,
            status: $data['status'] ?? null,
            search: $data['search'] ?? null,
        );
    }

    /** @return array<string, string|int> */
    public function toQuery(): array
    {
        return array_filter([
            'from' => $this->from?->toDateString(),
            'to' => $this->to?->toDateString(),
            'zone_id' => $this->zoneId,
            'flag' => $this->flag,
            'segment' => $this->segment,
            'status' => $this->status,
            'search' => $this->search,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
