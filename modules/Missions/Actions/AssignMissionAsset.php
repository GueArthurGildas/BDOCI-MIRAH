<?php

namespace Modules\Missions\Actions;

use App\Models\Asset;
use App\Models\Mission;
use App\Models\MissionAsset;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class AssignMissionAsset extends Action
{
    public function code(): string
    {
        return 'missions.assign_asset';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): MissionAsset
    {
        $mission = Mission::findOrFail($input['mission_id']);

        return $mission->missionAssets()->create([
            ...Arr::only($input, ['planned_fuel_l', 'budget']),
            'asset_id' => Asset::findOrFail($input['asset_id'])->id,
        ]);
    }
}
