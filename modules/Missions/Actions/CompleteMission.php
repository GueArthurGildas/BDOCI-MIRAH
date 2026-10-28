<?php

namespace Modules\Missions\Actions;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;
use Modules\Missions\Support\MissionReviewCalculator;

class CompleteMission extends Action
{
    public function __construct(private readonly MissionReviewCalculator $calculator) {}

    public function code(): string
    {
        return 'missions.complete';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): Mission
    {
        $mission = Mission::lockForUpdate()->findOrFail($input['mission_id']);

        if ($mission->status !== MissionStatus::InProgress) {
            throw ValidationException::withMessages(['mission' => __('missions::missions.errors.not_in_progress')]);
        }

        $mission->update(['status' => MissionStatus::Completed, 'actual_end_at' => now()]);
        ComputeMissionReview::persist($mission, $this->calculator);

        return $mission;
    }
}
