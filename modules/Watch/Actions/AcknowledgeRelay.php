<?php

namespace Modules\Watch\Actions;

use App\Models\Relay;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Auth\Access\AuthorizationException;

class AcknowledgeRelay extends Action
{
    public function code(): string
    {
        return 'watch.acknowledge_relay';
    }

    public function permission(): Permission
    {
        return Permission::WatchCreate;
    }

    public function handle(ActionContext $context, array $input): Relay
    {
        $relay = Relay::with('nextTeam')->findOrFail($input['relay_id']);
        $actor = $context->actor;

        if ($relay->next_team_id !== $actor->team_id && $relay->nextTeam?->lead_user_id !== $actor->id) {
            throw new AuthorizationException;
        }

        if ($relay->acknowledged_at === null) {
            $relay->update(['read_acknowledged_by' => $actor->id, 'acknowledged_at' => now()]);
        }

        return $relay;
    }
}
