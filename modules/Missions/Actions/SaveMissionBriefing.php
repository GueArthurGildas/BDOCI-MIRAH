<?php

namespace Modules\Missions\Actions;

use App\Models\Mission;
use App\Models\MissionBriefing;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class SaveMissionBriefing extends Action
{
    public function code(): string
    {
        return 'missions.save_briefing';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): MissionBriefing
    {
        $mission = Mission::findOrFail($input['mission_id']);

        $mission->preparation()->updateOrCreate([], ['checklist' => array_values(array_filter($input['checklist'] ?? []))]);

        return $mission->briefing()->updateOrCreate([], [
            'held_at' => filled($input['held_at'] ?? null) ? Carbon::parse($input['held_at']) : null,
            'notes' => $input['notes'] ?? null,
        ]);
    }
}
