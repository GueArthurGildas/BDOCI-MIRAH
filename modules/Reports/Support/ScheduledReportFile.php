<?php

namespace Modules\Reports\Support;

use App\Enums\ExportFormat;
use App\Enums\ReportFormat;
use App\Models\ScheduledReport;

final readonly class ScheduledReportFile
{
    public function __construct(public string $name, public ExportFormat $format) {}

    public static function for(ScheduledReport $report, ReportPeriod $period): self
    {
        $format = $report->format === ReportFormat::Pdf ? ExportFormat::Pdf : ExportFormat::Csv;

        return new self("{$report->code}_{$report->frequency->value}_{$period->label}.{$format->value}", $format);
    }
}
