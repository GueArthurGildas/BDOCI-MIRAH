<?php

namespace Modules\Reports\Notifications;

use App\Models\Document;
use App\Models\ScheduledReport;
use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class ScheduledReportReadyAlert extends Alert
{
    public function __construct(
        private readonly ScheduledReport $report,
        private readonly Document $document,
        private readonly string $period,
    ) {}

    public function key(): string
    {
        return "reports.report-ready.{$this->document->id}";
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Info;
    }

    protected function icon(): string
    {
        return 'icon-file-text';
    }

    protected function translationKey(): string
    {
        return 'reports::alerts.report_ready';
    }

    protected function params(): array
    {
        return ['count' => 1, 'report' => __("reports::reports.catalog.{$this->report->code}"), 'period' => $this->period];
    }

    protected function target(): array
    {
        return ['reports.index', []];
    }
}
