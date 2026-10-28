<?php

namespace Modules\Communications\Actions;

use App\Models\CommunicationChannel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class UpdateCommunicationChannel extends Action
{
    public function code(): string
    {
        return 'communications.update_channel';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsManageDispatch;
    }

    public function handle(ActionContext $context, array $input): CommunicationChannel
    {
        return tap(CommunicationChannel::findOrFail($input['id']))
            ->update(Arr::only($input, ['is_auto_dispatch_enabled', 'is_consent_required']));
    }
}
