<?php

namespace Modules\Missions\Actions;

use App\Models\Mission;
use App\Models\MissionEvent;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class AddMissionEvent extends Action
{
    public function code(): string
    {
        return 'missions.add_event';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): MissionEvent
    {
        return Mission::findOrFail($input['mission_id'])->events()->create([
            'occurred_at' => filled($input['occurred_at'] ?? null) ? Carbon::parse($input['occurred_at']) : now(),
            'body' => $input['body'],
        ]);
    }
}
