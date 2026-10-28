<?php

namespace Modules\Vessels\Actions;

use App\Enums\SourceSystem;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Vessels\SourcePriorityResolver;

class UpdateVessel extends Action
{
    public function __construct(private readonly SourcePriorityResolver $priorities) {}

    public function code(): string
    {
        return 'vessels.update';
    }

    public function permission(): Permission
    {
        return Permission::VesselsManage;
    }

    public function handle(ActionContext $context, array $input): Vessel
    {
        $vessel = Vessel::findOrFail($input['vessel_id']);
        $vessel->update($input['attributes']);

        foreach (array_keys($vessel->getChanges()) as $field) {
            if (in_array($field, config('registry.tracked_vessel_fields'), true)) {
                $this->priorities->recordProvenance($vessel, $field, SourceSystem::ManualEntry, true);
            }
        }

        $context->publish(
            vessel: $vessel,
            module: 'vessels',
            type: 'vessel.updated',
            subject: $vessel,
            summaryKey: 'vessels::events.updated',
            params: ['name' => $vessel->name],
        );

        return $vessel;
    }
}
