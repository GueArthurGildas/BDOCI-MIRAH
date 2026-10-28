<?php

namespace Modules\Communications\Actions;

use App\Enums\AiAgentActionStatus;
use App\Models\AiAgentAction;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\ActionCatalog;
use App\Support\Authorization\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Modules\Communications\Support\AiAgentAccount;

class RequestAiNote extends Action
{
    public function __construct(private readonly ActionRunner $actions, private readonly AiAgentAccount $agent) {}

    public function code(): string
    {
        return 'communications.request_ai_note';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsTriggerAiAgent;
    }

    public function handle(ActionContext $context, array $input): AiAgentAction
    {
        $parameters = Arr::only($input, ['type', 'vessel_id', 'urgency', 'recipient_user_id', 'recipient_team_id', 'due_date', 'relay']);

        $request = AiAgentAction::create([
            'action_id' => app(ActionCatalog::class)->idFor(Permission::CommunicationsCreateNote),
            'requested_by' => $context->actor->id,
            'instruction' => $input['instruction'],
            'parameters' => $parameters,
            'status' => AiAgentActionStatus::Requested->value,
        ]);

        $agent = $this->agent->user();

        if (! $agent || ! $this->agent->isEnabledFor(Permission::CommunicationsCreateNote)) {
            return tap($request)->update(['status' => AiAgentActionStatus::Refused->value]);
        }

        try {
            $note = $this->actions->run(CreateNote::class, $agent, [
                ...$parameters,
                'free_text' => $input['instruction'],
                'ai_agent_action_id' => $request->id,
            ], requestedBy: $context->actor);
        } catch (AuthorizationException) {
            return tap($request)->update(['status' => AiAgentActionStatus::Refused->value]);
        }

        return tap($request)->update([
            'status' => AiAgentActionStatus::Executed->value,
            'executed_at' => now(),
            'result_object_type' => $note->getMorphClass(),
            'result_object_id' => $note->id,
        ]);
    }
}
