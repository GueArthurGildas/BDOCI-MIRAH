<?php

namespace Modules\Missions\Actions;

use App\Models\Agent;
use App\Models\Mission;
use App\Models\MissionParticipant;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class AddMissionParticipant extends Action
{
    public function code(): string
    {
        return 'missions.add_participant';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): MissionParticipant
    {
        $mission = Mission::findOrFail($input['mission_id']);
        $agent = isset($input['agent_id']) ? Agent::findOrFail($input['agent_id']) : null;

        return $mission->participants()->create([
            ...Arr::only($input, ['role']),
            'agent_id' => $agent?->id,
            'agency' => $input['agency'] ?? $agent?->agency,
            'grade' => $input['grade'] ?? $agent?->grade,
            'is_mission_lead' => (bool) ($input['is_mission_lead'] ?? false),
        ]);
    }
}
