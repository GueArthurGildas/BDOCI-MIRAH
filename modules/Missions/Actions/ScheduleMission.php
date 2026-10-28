<?php

namespace Modules\Missions\Actions;

use App\Enums\MissionNature;
use App\Enums\MissionStatus;
use App\Enums\MissionType;
use App\Models\Agent;
use App\Models\Asset;
use App\Models\Mission;
use App\Models\Zone;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\Missions\Support\MissionNumbering;

class ScheduleMission extends Action
{
    public function __construct(private readonly MissionNumbering $numbering) {}

    public function code(): string
    {
        return 'missions.schedule';
    }

    public function permission(): Permission
    {
        return Permission::MissionsCreate;
    }

    public function handle(ActionContext $context, array $input): Mission
    {
        $start = Carbon::parse($input['planned_start_at']);

        $mission = Mission::create([
            ...Arr::only($input, ['origin', 'objectives', 'operation_id', 'alert_id', 'witness_report_id']),
            'code' => $this->numbering->code($start->year),
            'nature' => MissionNature::from($input['nature']),
            'type' => MissionType::from($input['type']),
            'planned_start_at' => $start,
            'planned_end_at' => filled($input['planned_end_at'] ?? null) ? Carbon::parse($input['planned_end_at']) : null,
            'zones' => Zone::whereIn('id', $input['zone_ids'] ?? [])->get(['id', 'name'])->map->only(['id', 'name'])->values()->all(),
            'points_gps' => $this->points($input['points_gps'] ?? ''),
            'status' => MissionStatus::Scheduled,
            'created_by' => $context->actor->id,
        ]);

        foreach (array_unique($input['agent_ids'] ?? []) as $agentId) {
            $agent = Agent::findOrFail($agentId);
            $mission->participants()->create([
                'agent_id' => $agent->id,
                'agency' => $agent->agency,
                'grade' => $agent->grade,
                'is_mission_lead' => (int) ($input['lead_agent_id'] ?? 0) === $agent->id,
            ]);
        }

        foreach (array_unique($input['asset_ids'] ?? []) as $assetId) {
            $mission->missionAssets()->create(['asset_id' => Asset::findOrFail($assetId)->id]);
        }

        return $mission;
    }

    /** @return array<int, array{latitude: float, longitude: float}> */
    private function points(string $text): array
    {
        return collect(preg_split('/\R/', trim($text)))
            ->filter()
            ->map(fn (string $line) => array_map('floatval', preg_split('/\s*[,;]\s*/', trim($line))))
            ->filter(fn (array $pair) => count($pair) === 2)
            ->map(fn (array $pair) => ['latitude' => $pair[0], 'longitude' => $pair[1]])
            ->values()
            ->all();
    }
}
