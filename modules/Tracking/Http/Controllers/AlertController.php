<?php

namespace Modules\Tracking\Http\Controllers;

use App\Enums\AlertStatus;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Operation;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Tracking\Actions\AcknowledgeAlert;
use Modules\Tracking\Actions\HandleAlert;
use Modules\Tracking\Http\Requests\HandleAlertRequest;

class AlertController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', Rule::enum(AlertStatus::class)]]);

        return view('tracking::alerts.index', [
            'filters' => $filters,
            'alerts' => Alert::query()->with(['vessel', 'rule', 'zone'])
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status), fn ($query) => $query->unresolved())
                ->latest('triggered_at')->paginate(self::PER_PAGE)->withQueryString(),
            'countsByStatus' => Alert::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Alert $alert): View
    {
        return view('tracking::alerts.show', [
            'alert' => $alert->load(['vessel', 'rule', 'zone', 'operation', 'acknowledger', 'handlings.handler']),
            'questions' => HandleAlert::QUESTIONS,
            'operations' => Operation::orderByDesc('started_at')->limit(50)->pluck('code', 'id'),
        ]);
    }

    public function acknowledge(Alert $alert, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AcknowledgeAlert::class, $request->user(), ['alert_id' => $alert->id]);

        return to_route('tracking.alerts.show', $alert)->with('status', __('tracking::tracking.alerts.acknowledged'));
    }

    public function handle(Alert $alert, HandleAlertRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(HandleAlert::class, $request->user(), [...$request->validated(), 'alert_id' => $alert->id]);

        return to_route('tracking.alerts.show', $alert)->with('status', __('tracking::tracking.alerts.handled'));
    }
}
