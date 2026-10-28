<?php

namespace Modules\Logbook\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LogbookEntry;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Logbook\Actions\AddLogbookEntry;
use Modules\Logbook\Actions\RectifyLogbookEntry;
use Modules\Logbook\Actions\SetLogbookEntryHandling;
use Modules\Logbook\Http\Requests\RectifyLogbookEntryRequest;
use Modules\Logbook\Http\Requests\SetLogbookEntryHandlingRequest;
use Modules\Logbook\Http\Requests\StoreLogbookEntryRequest;

class LogbookEntryController extends Controller
{
    public function store(StoreLogbookEntryRequest $request, ActionRunner $actions): RedirectResponse
    {
        $entry = $actions->run(AddLogbookEntry::class, $request->user(), $request->validated());

        return to_route('logbook.days.show', $entry->logbook_day_id)->with('status', __('logbook::logbook.entries.created'));
    }

    public function rectifyForm(LogbookEntry $entry): View
    {
        $entry->load(['day', 'channel', 'vessels']);

        return view('logbook::entries.rectify', compact('entry'));
    }

    public function rectify(RectifyLogbookEntryRequest $request, LogbookEntry $entry, ActionRunner $actions): RedirectResponse
    {
        $correction = $actions->run(RectifyLogbookEntry::class, $request->user(), [...$request->validated(), 'entry_id' => $entry->id]);

        return to_route('logbook.days.show', $correction->logbook_day_id)->with('status', __('logbook::logbook.entries.rectified'));
    }

    public function updateHandling(SetLogbookEntryHandlingRequest $request, LogbookEntry $entry, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SetLogbookEntryHandling::class, $request->user(), [...$request->validated(), 'entry_id' => $entry->id]);

        return to_route('logbook.days.show', $entry->logbook_day_id)->with('status', __('logbook::logbook.entries.handled'));
    }
}
