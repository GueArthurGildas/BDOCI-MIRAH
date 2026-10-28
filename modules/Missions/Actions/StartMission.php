<?php

namespace Modules\Missions\Actions;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class StartMission extends Action
{
    public function code(): string
    {
        return 'missions.start';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): Mission
    {
        $mission = Mission::lockForUpdate()->findOrFail($input['mission_id']);

        if ($mission->status !== MissionStatus::Authorized) {
            throw ValidationException::withMessages(['mission' => __('missions::missions.errors.not_authorized')]);
        }

        $mission->update(['status' => MissionStatus::InProgress, 'actual_start_at' => now()]);

        return $mission;
    }
}
