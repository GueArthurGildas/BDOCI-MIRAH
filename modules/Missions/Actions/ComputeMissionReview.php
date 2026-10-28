<?php

namespace Modules\Missions\Actions;

use App\Models\Mission;
use App\Models\MissionReview;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Modules\Missions\Support\MissionReviewCalculator;

class ComputeMissionReview extends Action
{
    public function __construct(private readonly MissionReviewCalculator $calculator) {}

    public function code(): string
    {
        return 'missions.compute_review';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): MissionReview
    {
        return self::persist(Mission::findOrFail($input['mission_id']), $this->calculator);
    }

    public static function persist(Mission $mission, MissionReviewCalculator $calculator): MissionReview
    {
        $review = MissionReview::firstOrNew(['mission_id' => $mission->id]);
        $review->fill([...$calculator->compute($mission), 'calculated_at' => now()])->save();

        return $review;
    }
}
