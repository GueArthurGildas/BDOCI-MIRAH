<?php

namespace Modules\Reports\Http\Controllers;

use App\Enums\ReportFormat;
use App\Enums\ReportFrequency;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\ScheduledReport;
use App\Models\User;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Reports\Actions\GenerateScheduledReport;
use Modules\Reports\Actions\SaveScheduledReport;
use Modules\Reports\Http\Requests\SaveScheduledReportRequest;
use Modules\Reports\Support\ReportCatalog;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScheduledReportController extends Controller
{
    public function index(ReportCatalog $catalog, DocumentStore $documents): View
    {
        $reports = ScheduledReport::with('recipient')->orderBy('id')->get();

        return view('reports::scheduled.index', [
            'reports' => $reports,
            'documents' => $reports->mapWithKeys(fn (ScheduledReport $report) => [$report->id => $documents->for($report)->take(3)]),
            'catalog' => $catalog->options(),
            'frequencies' => ReportFrequency::options(),
            'formats' => ReportFormat::options(),
            'recipients' => User::where('is_active', true)->where('type', 'human')->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(SaveScheduledReportRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveScheduledReport::class, $request->user(), [...$request->validated(), 'is_active' => true]);

        return to_route('reports.index')->with('status', __('reports::reports.scheduled.created'));
    }

    public function update(SaveScheduledReportRequest $request, ScheduledReport $scheduledReport, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveScheduledReport::class, $request->user(), [
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'scheduled_report_id' => $scheduledReport->id,
        ]);

        return to_route('reports.index')->with('status', __('reports::reports.scheduled.updated'));
    }

    public function generate(ScheduledReport $scheduledReport, ActionRunner $actions): RedirectResponse
    {
        $document = $actions->run(GenerateScheduledReport::class, request()->user(), ['scheduled_report_id' => $scheduledReport->id]);

        return $document
            ? to_route('reports.index')->with('status', __('reports::reports.scheduled.generated'))
            : to_route('reports.index')->with('error', __('reports::reports.scheduled.already_generated'));
    }

    public function download(Document $document): StreamedResponse
    {
        abort_unless(
            DB::table('document_links')->where('document_id', $document->id)->where('object_type', (new ScheduledReport)->getMorphClass())->exists(),
            404,
        );

        return Storage::disk(DocumentStore::DISK)->download($document->path, $document->name, ['Content-Type' => $document->mime_type]);
    }
}
