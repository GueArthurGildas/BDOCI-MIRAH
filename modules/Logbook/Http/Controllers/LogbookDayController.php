<?php

namespace Modules\Logbook\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LogbookDay;
use App\Models\Operation;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookCalendar;
use App\Support\Logbook\LogbookChannels;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Logbook\Actions\CloseLogbookDay;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogbookDayController extends Controller
{
    private const PER_PAGE = 20;

    public function index(): View
    {
        return view('logbook::days.index', [
            'days' => LogbookDay::query()->withCount('entries')->latest('day')->paginate(self::PER_PAGE),
        ]);
    }

    public function today(LogbookCalendar $calendar): RedirectResponse
    {
        return to_route('logbook.days.show', $calendar->today());
    }

    public function show(LogbookDay $day, LogbookChannels $channels, DocumentStore $documents): View
    {
        $day->load(['entries.channel', 'entries.vessels', 'entries.operation', 'entries.author', 'entries.corrects', 'entries.corrections', 'closing.closer', 'pdfDocument']);

        return view('logbook::days.show', [
            'day' => $day,
            'channels' => $channels->options(),
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'operations' => Operation::query()->latest('started_at')->limit(100)->pluck('code', 'id'),
            'pdfIntact' => $day->pdfDocument ? $documents->isIntact($day->pdfDocument) : null,
        ]);
    }

    public function close(LogbookDay $day, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CloseLogbookDay::class, request()->user(), ['day_id' => $day->id]);

        return to_route('logbook.days.show', $day)->with('status', __('logbook::logbook.days.closed'))->with('popup', true);
    }

    public function pdf(LogbookDay $day): StreamedResponse
    {
        abort_if($day->pdfDocument === null, 404);

        return Storage::disk(DocumentStore::DISK)->download($day->pdfDocument->path, $day->pdfDocument->name, ['Content-Type' => 'application/pdf']);
    }
}
