<?php

namespace Modules\Communications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Communications\Actions\SetAiAgentEnabled;
use Modules\Communications\Http\Requests\AiAgentRequest;
use Modules\Communications\Support\AiAgentAccount;

class AiAgentController extends Controller
{
    public function edit(AiAgentAccount $account): View
    {
        return view('communications::ai-agent.edit', [
            'enabled' => $account->isEnabledFor(Permission::CommunicationsCreateNote),
            'account' => $account->user(),
        ]);
    }

    public function update(AiAgentRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SetAiAgentEnabled::class, $request->user(), $request->validated());

        return to_route('communications.ai-agent.edit')->with('status', __('communications::ai_agent.saved'));
    }
}
