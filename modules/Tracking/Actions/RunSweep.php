<?php

namespace Modules\Tracking\Actions;

use App\Enums\SlotTestResult;
use App\Enums\SweepAnomalyType;
use App\Enums\VesselCategory;
use App\Models\ControlSlot;
use App\Models\License;
use App\Models\Sweep;
use App\Models\SweepAnomaly;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RunSweep extends Action
{
    public function code(): string
    {
        return 'tracking.run_sweep';
    }

    public function permission(): Permission
    {
        return Permission::TrackingCreate;
    }

    public function handle(ActionContext $context, array $input): Sweep
    {
        $slot = ControlSlot::with('slotPositions')->findOrFail($input['control_slot_id']);
        $positions = $slot->slotPositions;
        $nonEmitting = $positions->where('emission_test', SlotTestResult::Ko);

        $sweep = Sweep::create([
            'control_slot_id' => $slot->id,
            'operator_user_id' => $context->actor->id,
            'vessels_count' => $positions->count(),
            'non_emitting_count' => $nonEmitting->count(),
        ]);

        foreach ($nonEmitting as $position) {
            $this->anomaly($context, $sweep, $position->vessel_id, SweepAnomalyType::NonEmitting);
        }

        $absent = License::whereNull('suspended_at')->where('valid_until', '>=', today())->whereHas('vessel', fn ($query) => $query->where('category', VesselCategory::Fishing))
            ->pluck('vessel_id')->unique()->diff($positions->pluck('vessel_id'));

        foreach ($absent as $vesselId) {
            $this->anomaly($context, $sweep, $vesselId, SweepAnomalyType::LicensedAbsentThemis);
        }

        return $sweep;
    }

    private function anomaly(ActionContext $context, Sweep $sweep, int $vesselId, SweepAnomalyType $type): void
    {
        $anomaly = SweepAnomaly::create(['sweep_id' => $sweep->id, 'vessel_id' => $vesselId, 'type' => $type]);

        $context->publish(
            vessel: $vesselId,
            module: 'tracking',
            type: 'sweep.anomaly',
            subject: $anomaly,
            summaryKey: 'tracking::events.sweep_anomaly',
            params: ['type' => $type->value],
        );
    }
}
