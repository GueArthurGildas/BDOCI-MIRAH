<?php

namespace Modules\Movements\Http\Controllers;

use App\Enums\EntryExitAlertStatus;
use App\Http\Controllers\Controller;
use App\Models\EntryExitAlert;
use App\Support\Actions\ActionRunner;
use App\Support\Tables\PerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Movements\Actions\CloseEntryExitAlert;

class EntryExitAlertController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', Rule::enum(EntryExitAlertStatus::class)]]);

        $alerts = EntryExitAlert::query()
            ->with(['vessel', 'declaration'])
            ->where('status', $filters['status'] ?? EntryExitAlertStatus::Open->value)
            ->latest('alerted_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('movements::eez.alerts', ['alerts' => $alerts, 'filters' => $filters]);
    }

    public function close(EntryExitAlert $alert, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CloseEntryExitAlert::class, $request->user(), ['entry_exit_alert_id' => $alert->id]);

        return back()->with('status', __('movements::eez.alert_closed'));
    }
}
