<?php

namespace Modules\Missions\Actions;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;
use Modules\Missions\Support\MissionReviewCalculator;

class CloseMission extends Action
{
    public function __construct(private readonly MissionReviewCalculator $calculator) {}

    public function code(): string
    {
        return 'missions.close';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): Mission
    {
        $mission = Mission::lockForUpdate()->findOrFail($input['mission_id']);

        if ($mission->status !== MissionStatus::Completed) {
            throw ValidationException::withMessages(['mission' => __('missions::missions.errors.not_completed')]);
        }

        ComputeMissionReview::persist($mission, $this->calculator);
        $mission->update(['status' => MissionStatus::Closed]);

        return $mission;
    }
}
