<?php

namespace Modules\Reports\Support;

use App\Enums\ReportFrequency;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final readonly class ReportPeriod
{
    public function __construct(public CarbonImmutable $start, public CarbonImmutable $end, public string $label) {}

    public static function previous(ReportFrequency $frequency, CarbonInterface $now): self
    {
        $reference = CarbonImmutable::instance($now);

        return match ($frequency) {
            ReportFrequency::Weekly => self::between($reference->subWeek()->startOfWeek(), $reference->subWeek()->endOfWeek(), 'o-\WW'),
            ReportFrequency::Monthly => self::between($reference->subMonthNoOverflow()->startOfMonth(), $reference->subMonthNoOverflow()->endOfMonth(), 'Y-m'),
            ReportFrequency::Yearly => self::between($reference->subYearNoOverflow()->startOfYear(), $reference->subYearNoOverflow()->endOfYear(), 'Y'),
        };
    }

    private static function between(CarbonImmutable $start, CarbonImmutable $end, string $labelFormat): self
    {
        return new self($start, $end, $start->format($labelFormat));
    }
}
