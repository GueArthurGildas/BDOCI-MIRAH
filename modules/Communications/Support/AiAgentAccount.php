<?php

namespace Modules\Communications\Support;

use App\Enums\UserType;
use App\Models\ActionCatalogEntry;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Support\Authorization\ActionCatalog;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;

class AiAgentAccount
{
    public const GROUP = 'ai_agent_notes';

    public function user(): ?User
    {
        return User::where('type', UserType::AiAgent->value)->where('is_active', true)->orderBy('id')->first();
    }

    public function isEnabledFor(Permission $permission): bool
    {
        return ActionCatalogEntry::where('code', $permission->value)
            ->where('ai_agent_allowed', true)
            ->where('ai_agent_enabled', true)
            ->exists();
    }

    public function provision(): User
    {
        $email = config('communications.ai_agent_email');
        $user = User::where('email', $email)->first()
            ?? User::forceCreate(['name' => 'AI agent', 'email' => $email, 'type' => UserType::AiAgent->value]);

        $group = PermissionGroup::firstOrCreate(['code' => self::GROUP], ['label' => 'AI agent account', 'is_system' => false]);
        app(GrantService::class)->syncGroup($group, [Permission::CommunicationsCreateNote->value]);
        $user->permissionGroups()->syncWithoutDetaching([$group->id]);

        $this->enable(Permission::CommunicationsCreateNote);

        return $user;
    }

    public function enable(Permission $permission, bool $enabled = true): void
    {
        app(ActionCatalog::class)->idFor($permission);

        ActionCatalogEntry::where('code', $permission->value)->update(['ai_agent_allowed' => true, 'ai_agent_enabled' => $enabled]);
    }
}
