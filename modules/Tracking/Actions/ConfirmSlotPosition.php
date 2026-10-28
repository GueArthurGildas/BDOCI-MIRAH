<?php

namespace Modules\Tracking\Actions;

use App\Enums\SlotResult;
use App\Enums\SlotTestResult;
use App\Models\SlotPosition;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class ConfirmSlotPosition extends Action
{
    public function code(): string
    {
        return 'tracking.confirm_slot_position';
    }

    public function permission(): Permission
    {
        return Permission::TrackingCreate;
    }

    public function handle(ActionContext $context, array $input): SlotPosition
    {
        $position = SlotPosition::findOrFail($input['slot_position_id']);

        $tests = [
            'emission_test' => SlotTestResult::from($input['emission_test']),
            'speed_activity_test' => SlotTestResult::from($input['speed_activity_test']),
            'trajectory_test' => SlotTestResult::from($input['trajectory_test']),
        ];

        $position->update([
            ...$tests,
            'result' => isset($input['result']) ? SlotResult::from($input['result']) : $this->derivedResult($tests),
            'confirmed_by' => $context->actor->id,
        ]);

        $context->publish(
            vessel: $position->vessel_id,
            module: 'tracking',
            type: 'slot_position.confirmed',
            subject: $position,
            summaryKey: 'tracking::events.slot_position_confirmed',
            params: ['result' => $position->result->value],
        );

        return $position;
    }

    /** @param array<string, SlotTestResult> $tests */
    private function derivedResult(array $tests): SlotResult
    {
        return in_array(SlotTestResult::Ko, $tests, true) ? SlotResult::Suspect : SlotResult::Normal;
    }
}
