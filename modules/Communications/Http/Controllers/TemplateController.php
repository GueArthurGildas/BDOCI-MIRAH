<?php

namespace Modules\Communications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Communications\Actions\SaveMessageTemplate;
use Modules\Communications\Http\Requests\SaveTemplateRequest;

class TemplateController extends Controller
{
    public function index(): View
    {
        return view('communications::templates.index', ['templates' => MessageTemplate::orderBy('code')->get()]);
    }

    public function create(): View
    {
        return view('communications::templates.form', ['template' => new MessageTemplate(['language' => 'fr', 'form' => 'none', 'is_active' => true])]);
    }

    public function store(SaveTemplateRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveMessageTemplate::class, $request->user(), $request->validated());

        return to_route('communications.templates.index')->with('status', __('communications::templates.saved'));
    }

    public function edit(MessageTemplate $template): View
    {
        return view('communications::templates.form', ['template' => $template]);
    }

    public function update(MessageTemplate $template, SaveTemplateRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveMessageTemplate::class, $request->user(), [...$request->validated(), 'id' => $template->id]);

        return to_route('communications.templates.index')->with('status', __('communications::templates.saved'));
    }
}
