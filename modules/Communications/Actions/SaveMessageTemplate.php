<?php

namespace Modules\Communications\Actions;

use App\Models\MessageTemplate;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class SaveMessageTemplate extends Action
{
    public function code(): string
    {
        return 'communications.save_template';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsManageTemplates;
    }

    public function handle(ActionContext $context, array $input): MessageTemplate
    {
        $attributes = Arr::only($input, ['code', 'channel', 'language', 'subject', 'body', 'form', 'is_active']);

        if (isset($input['id'])) {
            return tap(MessageTemplate::findOrFail($input['id']))->update($attributes);
        }

        return MessageTemplate::create($attributes);
    }
}
