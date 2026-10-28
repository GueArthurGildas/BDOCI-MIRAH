<?php

namespace Modules\Missions\Actions;

use App\Models\Mission;
use App\Models\MissionDebriefing;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class SaveMissionDebriefing extends Action
{
    public function code(): string
    {
        return 'missions.save_debriefing';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): MissionDebriefing
    {
        $mission = Mission::findOrFail($input['mission_id']);

        return $mission->debriefing()->updateOrCreate([], [
            'held_at' => filled($input['held_at'] ?? null) ? Carbon::parse($input['held_at']) : now(),
            'notes' => $input['notes'] ?? null,
        ]);
    }
}
