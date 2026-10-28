<?php

namespace Modules\Communications\Actions;

use App\Models\ActionCatalogEntry;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Modules\Communications\Support\AiAgentAccount;

class SetAiAgentEnabled extends Action
{
    public function __construct(private readonly AiAgentAccount $account) {}

    public function code(): string
    {
        return 'communications.set_ai_agent_enabled';
    }

    public function permission(): Permission
    {
        return Permission::SettingsManage;
    }

    public function handle(ActionContext $context, array $input): ActionCatalogEntry
    {
        $this->account->enable(Permission::CommunicationsCreateNote, (bool) $input['enabled']);

        return ActionCatalogEntry::where('code', Permission::CommunicationsCreateNote->value)->firstOrFail();
    }
}
