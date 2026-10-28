<?php

namespace Modules\Communications\Actions;

use App\Models\DispatchRule;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;

class SaveDispatchRule extends Action
{
    public function code(): string
    {
        return 'communications.save_dispatch_rule';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsManageDispatch;
    }

    public function handle(ActionContext $context, array $input): DispatchRule
    {
        $attributes = [
            ...Arr::only($input, ['message_template_id', 'trigger_event', 'frequency_minutes', 'daily_cap', 'stop_condition', 'mode', 'is_active', 'editable_by_permission_group_id']),
            'conditions' => ['recipient' => $input['recipient'] ?? null],
        ];

        if (! isset($input['id'])) {
            return DispatchRule::create($attributes);
        }

        $rule = DispatchRule::findOrFail($input['id']);

        if ($rule->editable_by_permission_group_id && ! $context->actor->permissionGroups()->whereKey($rule->editable_by_permission_group_id)->exists()) {
            throw new AuthorizationException;
        }

        return tap($rule)->update($attributes);
    }
}
