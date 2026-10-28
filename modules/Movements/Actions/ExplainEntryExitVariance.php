<?php

namespace Modules\Movements\Actions;

use App\Models\EntryExitLink;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class ExplainEntryExitVariance extends Action
{
    public function code(): string
    {
        return 'movements.explain_entry_exit_variance';
    }

    public function permission(): Permission
    {
        return Permission::MovementsDeclare;
    }

    public function handle(ActionContext $context, array $input): EntryExitLink
    {
        $link = EntryExitLink::with('exit')->findOrFail($input['entry_exit_link_id']);
        $link->update(['explanation' => $input['explanation']]);

        if ($link->exit->vessel_id !== null) {
            $context->publish(
                vessel: $link->exit->vessel_id,
                module: 'movements',
                type: 'entry_exit_link.explained',
                subject: $link,
                summaryKey: 'movements::events.variance_explained',
                params: ['explanation' => $link->explanation->value, 'variance_kg' => $link->variance_kg],
            );
        }

        return $link;
    }
}
