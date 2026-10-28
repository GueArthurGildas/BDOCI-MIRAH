<?php

namespace Modules\Missions\Actions;

use App\Models\MissionAsset;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class RecordAssetUsage extends Action
{
    public function code(): string
    {
        return 'missions.record_asset_usage';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): MissionAsset
    {
        $missionAsset = MissionAsset::findOrFail($input['mission_asset_id']);
        $missionAsset->update(Arr::only($input, ['engine_hours', 'nautical_miles', 'consumed_fuel_l', 'state', 'breakdown']));

        return $missionAsset;
    }
}
