<?php

namespace Modules\Infractions\Support;

use App\Support\Numbering\YearlySequence;

final class Numbering
{
    private const CASE_LOCK_KEY = 6001;

    private const VIOLATION_REPORT_LOCK_KEY = 6002;

    public function __construct(private readonly YearlySequence $sequence) {}

    public function caseReference(int $year): string
    {
        return $this->sequence->next('DI', $year, 'infraction_cases', 'reference', self::CASE_LOCK_KEY);
    }

    public function violationReportNumber(int $year): string
    {
        return $this->sequence->next('PV', $year, 'violation_reports', 'number', self::VIOLATION_REPORT_LOCK_KEY);
    }
}
