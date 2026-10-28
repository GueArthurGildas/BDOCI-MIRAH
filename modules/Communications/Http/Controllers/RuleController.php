<?php

namespace Modules\Communications\Http\Controllers;

use App\Enums\DispatchMode;
use App\Http\Controllers\Controller;
use App\Models\DispatchRule;
use App\Models\MessageTemplate;
use App\Models\PermissionGroup;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Communications\Actions\SaveDispatchRule;
use Modules\Communications\Http\Requests\SaveRuleRequest;

class RuleController extends Controller
{
    public function index(): View
    {
        return view('communications::rules.index', ['rules' => DispatchRule::with('template')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('communications::rules.form', $this->formData(new DispatchRule(['mode' => DispatchMode::WithValidation, 'is_active' => false])));
    }

    public function store(SaveRuleRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveDispatchRule::class, $request->user(), $request->validated());

        return to_route('communications.rules.index')->with('status', __('communications::rules.saved'));
    }

    public function edit(DispatchRule $rule): View
    {
        return view('communications::rules.form', $this->formData($rule));
    }

    public function update(DispatchRule $rule, SaveRuleRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveDispatchRule::class, $request->user(), [...$request->validated(), 'id' => $rule->id]);

        return to_route('communications.rules.index')->with('status', __('communications::rules.saved'));
    }

    /** @return array<string, mixed> */
    private function formData(DispatchRule $rule): array
    {
        return [
            'rule' => $rule,
            'templates' => MessageTemplate::orderBy('code')->pluck('code', 'id')->all(),
            'groups' => PermissionGroup::orderBy('code')->get()->mapWithKeys(fn (PermissionGroup $group) => [$group->id => $group->displayLabel()])->all(),
        ];
    }
}
