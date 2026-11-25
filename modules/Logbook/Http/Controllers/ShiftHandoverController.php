<?php

namespace Modules\Logbook\Http\Controllers;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\StandingInstruction;
use App\Models\User;
use App\Support\Actions\ActionRunner;
use App\Support\Logbook\CspManager;
use App\Support\Logbook\LogbookCalendar;
use App\Support\Logbook\OperationSnapshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Logbook\Actions\RecordShiftHandover;
use Modules\Logbook\Http\Requests\StoreShiftHandoverRequest;

class ShiftHandoverController extends Controller
{
    public function create(LogbookCalendar $calendar, OperationSnapshot $operations, CspManager $manager): View
    {
        $day = $calendar->today();

        return view('logbook::handovers.create', [
            'day' => $day,
            'users' => User::query()
                ->where('is_active', true)
                ->where('type', UserType::Human)
                ->whereKeyNot(request()->user()->id)
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (User $user) => [$user->id => $user->fullName()]),
            'manager' => $manager->name(),
            'operationCounters' => $operations->lastNumbers(now()->year),
            'openOperations' => $operations->openOperations(),
            'standingInstructions' => StandingInstruction::query()
                ->whereDate('started_on', '<=', today())
                ->where(fn ($query) => $query->whereNull('ended_on')->orWhereDate('ended_on', '>=', today()))
                ->with('vessel')
                ->latest('started_on')
                ->get(),
        ]);
    }

    public function store(StoreShiftHandoverRequest $request, ActionRunner $actions): RedirectResponse
    {
        $handover = $actions->run(RecordShiftHandover::class, $request->user(), $request->validated());

        return to_route('logbook.days.show', $handover->logbook_day_id)->with('status', __('logbook::logbook.handovers.recorded'))->with('popup', true);
    }
}
