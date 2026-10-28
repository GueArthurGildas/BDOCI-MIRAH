<?php

namespace Modules\Missions\Actions;

use App\Enums\AssetType;
use App\Models\Asset;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class SaveAsset extends Action
{
    public function code(): string
    {
        return 'missions.save_asset';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): Asset
    {
        return Asset::create([
            'type' => AssetType::from($input['type']),
            'label' => $input['label'],
            'is_active' => true,
        ]);
    }
}
