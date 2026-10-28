<?php

namespace Modules\Reports\Actions;

use App\Models\Document;
use App\Models\ScheduledReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use Illuminate\Support\Facades\App;
use Modules\Reports\Notifications\ScheduledReportReadyAlert;
use Modules\Reports\Support\ExportRenderer;
use Modules\Reports\Support\ReportCatalog;
use Modules\Reports\Support\ReportPeriod;
use Modules\Reports\Support\ScheduledReportFile;

class GenerateScheduledReport extends Action
{
    public function __construct(
        private readonly DocumentStore $documents,
        private readonly ReportCatalog $catalog,
        private readonly ExportRenderer $renderer,
    ) {}

    public function code(): string
    {
        return 'reports.generate';
    }

    public function permission(): Permission
    {
        return Permission::ReportsGenerate;
    }

    public function handle(ActionContext $context, array $input): ?Document
    {
        $report = ScheduledReport::with('recipient')->findOrFail($input['scheduled_report_id']);
        $period = ReportPeriod::previous($report->frequency, $input['now'] ?? now());
        $file = ScheduledReportFile::for($report, $period);

        if ($this->documents->for($report)->contains('name', $file->name)) {
            return null;
        }

        $rendered = $this->render($report, $period, $file);

        $document = $this->documents->storeContent($file->name, $rendered->content, $rendered->mimeType, $context->actor, freeze: true);
        $this->documents->attach($document, $report);

        if (config('surveillance.alerts.scheduled_reports.enabled') && $report->recipient->is_active) {
            $report->recipient->notify(new ScheduledReportReadyAlert($report, $document, $period->label));
        }

        return $document;
    }

    private function render(ScheduledReport $report, ReportPeriod $period, ScheduledReportFile $file)
    {
        $previous = App::getLocale();
        App::setLocale(config('reports.document_locale'));

        try {
            return $this->renderer->render($this->catalog->build($report->code, $period), $file->format);
        } finally {
            App::setLocale($previous);
        }
    }
}
