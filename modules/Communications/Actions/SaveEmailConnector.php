<?php

namespace Modules\Communications\Actions;

use App\Models\EmailConnector;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class SaveEmailConnector extends Action
{
    public function code(): string
    {
        return 'communications.save_email_connector';
    }

    public function permission(): Permission
    {
        return Permission::ConnectorsManage;
    }

    public function handle(ActionContext $context, array $input): EmailConnector
    {
        $connector = EmailConnector::latest('id')->first() ?? new EmailConnector;

        $connector->fill([
            'supplier' => $input['supplier'],
            'domain' => $input['domain'] ?? null,
            'parameters' => ['mailer' => $input['mailer'] ?? null],
            'is_active' => $input['is_active'],
        ])->save();

        return $connector;
    }
}
