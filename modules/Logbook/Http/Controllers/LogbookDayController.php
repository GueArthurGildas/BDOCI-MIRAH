<?php

namespace Modules\Logbook\Http\Controllers;

use App\Enums\LogbookDayStatus;
use App\Http\Controllers\Controller;
use App\Models\LogbookDay;
use App\Models\Operation;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookCalendar;
use App\Support\Logbook\LogbookChannels;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Logbook\Actions\CloseLogbookDay;
use Modules\Logbook\Support\LogbookCounters;
use Modules\Logbook\Support\LogbookDayFolders;
use Modules\Logbook\Support\LogbookDayWord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogbookDayController extends Controller
{
    private const PER_PAGE = 10;

    private const FOLDER_DAYS_LIMIT = 400;

    public function index(Request $request, LogbookChannels $channels, LogbookCounters $counters): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(LogbookDayStatus::class)],
            'channel' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'view' => ['nullable', Rule::in(['list', 'folders'])],
            'day' => ['nullable', 'integer', 'exists:logbook_days,id'],
            'folder' => ['nullable', Rule::in(LogbookDayFolders::FOLDERS)],
            'item' => ['nullable', 'integer'],
        ]);

        $query = LogbookDay::query()->withCount('entries')
            ->when($filters['search'] ?? null, fn (Builder $days, string $term) => $days->whereHas('entries', fn (Builder $entries) => $entries->where(fn (Builder $inner) => $inner
                ->whereLike('content', "%{$term}%")
                ->orWhereHas('vessels', fn (Builder $vessels) => $vessels->search($term))
                ->orWhereHas('author', fn (Builder $authors) => $authors->whereLike('name', "%{$term}%")))))
            ->when($filters['status'] ?? null, fn (Builder $days, string $status) => $days->where('status', $status))
            ->when($filters['channel'] ?? null, fn (Builder $days, int|string $channel) => $days->whereHas('entries', fn (Builder $entries) => $entries->where('channel_id', $channel)))
            ->when($filters['from'] ?? null, fn (Builder $days, string $from) => $days->whereDate('day', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $days, string $to) => $days->whereDate('day', '<=', $to))
            ->latest('day');

        $view = $filters['view'] ?? 'list';
        $data = ['filters' => $filters, 'view' => $view, 'channels' => $channels->options(), 'statuses' => LogbookDayStatus::options()];

        if ($view === 'list') {
            return view('logbook::days.index', $data + ['days' => $query->paginate(PerPage::from($request, self::PER_PAGE))->withQueryString()]);
        }

        $days = $query->limit(self::FOLDER_DAYS_LIMIT)->get();
        $selected = $days->firstWhere('id', $filters['day'] ?? null) ?? $days->first();
        $folder = $filters['folder'] ?? 'summary';
        $item = isset($filters['item']) ? (int) $filters['item'] : null;

        return view('logbook::days.index', $data + [
            'days' => $days,
            'tree' => $days->groupBy(fn (LogbookDay $day) => $day->day->year)->map(fn ($yearDays) => $yearDays->groupBy(fn (LogbookDay $day) => $day->day->format('Y-m'))),
            'selected' => $selected,
            'folder' => $folder,
            'item' => $item,
            'content' => $selected ? LogbookDayFolders::for($selected, $folder, $item) : null,
            'counters' => $selected ? $counters->for($selected) : null,
        ]);
    }

    public function word(LogbookDay $day, LogbookDayWord $word): Response
    {
        return response($word->render($day), 200, [
            'Content-Type' => LogbookDayWord::MIME_TYPE,
            'Content-Disposition' => 'attachment; filename="'.$word->filename($day).'"',
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
