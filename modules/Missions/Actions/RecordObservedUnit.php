<?php

namespace Modules\Missions\Actions;

use App\Enums\MissionStatus;
use App\Enums\ObservedUnitLevel;
use App\Models\Mission;
use App\Models\ObservedUnit;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RecordObservedUnit extends Action
{
    public function code(): string
    {
        return 'missions.record_observed_unit';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): ObservedUnit
    {
        $mission = Mission::findOrFail($input['mission_id']);
        $level = ObservedUnitLevel::from($input['level']);

        if (in_array($mission->status, [MissionStatus::Scheduled, MissionStatus::Closed], true)) {
            throw ValidationException::withMessages(['mission' => __('missions::missions.errors.units_not_allowed')]);
        }

        if ($level === ObservedUnitLevel::Inspected && blank($input['inspection_id'] ?? null)) {
            throw ValidationException::withMessages(['inspection_id' => __('missions::missions.errors.inspection_required')]);
        }

        $vessel = $this->vessel($input);
        $observedAt = filled($input['observed_at'] ?? null) ? Carbon::parse($input['observed_at']) : now();

        $unit = $mission->observedUnits()->create([
            ...Arr::only($input, ['sighting_source', 'recorded_name', 'recorded_marks', 'observed_activity', 'latitude', 'longitude', 'inspection_id']),
            'vessel_id' => $vessel?->id,
            'level' => $level,
            'observed_at' => $observedAt,
        ]);

        if ($vessel) {
            $context->publish(
                vessel: $vessel,
                module: 'missions',
                type: 'mission.unit_observed',
                subject: $unit,
                summaryKey: 'missions::events.unit_observed.'.$level->value,
                params: ['code' => $mission->code],
                occurredAt: $observedAt,
            );
        }

        return $unit;
    }

    /** @param array<string, mixed> $input */
    private function vessel(array $input): ?Vessel
    {
        if (filled($input['vessel_id'] ?? null)) {
            return Vessel::findOrFail($input['vessel_id']);
        }

        return filled($input['recorded_marks'] ?? null)
            ? Vessel::where('registration_number', $input['recorded_marks'])->first()
            : null;
    }
}
