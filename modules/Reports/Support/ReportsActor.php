<?php

namespace Modules\Reports\Support;

use App\Enums\UserType;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;

class ReportsActor
{
    public const GROUP = 'scheduled_reports';

    public function resolve(): User
    {
        $email = config('reports.system_actor_email');
        $user = User::where('email', $email)->first()
            ?? User::forceCreate(['name' => 'System', 'email' => $email, 'type' => UserType::System->value]);

        $group = PermissionGroup::firstOrCreate(['code' => self::GROUP], ['is_system' => true]);
        app(GrantService::class)->syncGroup($group, [Permission::ReportsGenerate->value, Permission::ReportsArchive->value]);
        $user->permissionGroups()->syncWithoutDetaching([$group->id]);

        return $user;
    }
}
