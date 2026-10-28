<?php

namespace Modules\Missions\Actions;

use App\Enums\AuthorizingRole;
use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\MissionAuthorization;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Modules\Missions\Support\MissionAuthorizationRule;

class AuthorizeMission extends Action
{
    public function code(): string
    {
        return 'missions.authorize';
    }

    public function permission(): Permission
    {
        return Permission::MissionsAuthorize;
    }

    public function handle(ActionContext $context, array $input): MissionAuthorization
    {
        $mission = Mission::lockForUpdate()->findOrFail($input['mission_id']);

        if ($mission->status !== MissionStatus::Scheduled) {
            throw ValidationException::withMessages(['mission' => __('missions::missions.errors.not_scheduled')]);
        }

        $isDirector = $context->actor->can(Permission::MissionsAuthorizeAsDirector->value);

        if (! $isDirector && ! MissionAuthorizationRule::teamLeadMayAuthorize($mission->type)) {
            throw new AuthorizationException;
        }

        $authorization = $mission->authorizations()->create([
            'authorized_by' => $context->actor->id,
            'authorizing_role' => $isDirector ? AuthorizingRole::Director : AuthorizingRole::TeamLead,
            'authorized_at' => now(),
        ]);

        $mission->update(['status' => MissionStatus::Authorized]);

        return $authorization;
    }
}
