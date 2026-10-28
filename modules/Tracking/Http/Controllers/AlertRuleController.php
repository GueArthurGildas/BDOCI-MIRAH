<?php

namespace Modules\Tracking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AlertRule;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Tracking\Actions\SaveAlertRule;
use Modules\Tracking\Http\Requests\SaveAlertRuleRequest;

class AlertRuleController extends Controller
{
    public function index(): View
    {
        return view('tracking::rules.index', ['rules' => AlertRule::orderBy('id')->get()]);
    }

    public function store(SaveAlertRuleRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveAlertRule::class, $request->user(), $request->validated());

        return to_route('tracking.rules.index')->with('status', __('tracking::tracking.rules.saved'));
    }

    public function update(AlertRule $rule, SaveAlertRuleRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveAlertRule::class, $request->user(), [...$request->validated(), 'alert_rule_id' => $rule->id]);

        return to_route('tracking.rules.index')->with('status', __('tracking::tracking.rules.saved'));
    }
}
