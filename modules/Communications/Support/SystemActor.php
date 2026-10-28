<?php

namespace Modules\Communications\Support;

use App\Enums\UserType;
use App\Models\PermissionGroup;
use App\Models\User;

class SystemActor
{
    public function resolve(): User
    {
        $email = config('communications.system_actor_email');
        $user = User::where('email', $email)->first()
            ?? User::forceCreate(['name' => 'System', 'email' => $email, 'type' => UserType::System->value]);

        $group = PermissionGroup::firstOrCreate(['code' => 'automatic_dispatch'], ['is_system' => true]);
        $user->permissionGroups()->syncWithoutDetaching([$group->id]);

        return $user;
    }
}
