<?php

namespace Modules\Vms\Actions;

use App\Enums\VmsFailureCategory;
use App\Models\VmsFailure;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class DeclareVmsFailure extends Action
{
    public function code(): string
    {
        return 'vms.declare_failure';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): VmsFailure
    {
        $category = VmsFailureCategory::from($input['category']);

        $failure = VmsFailure::create([
            'vessel_id' => $input['vessel_id'],
            'category' => $category,
            'started_at' => isset($input['started_at']) ? Carbon::parse($input['started_at']) : now(),
            'report_frequency_h' => $input['report_frequency_h'] ?? config('surveillance.vms.failure_report_frequency_hours.'.$category->value),
            'repair_deadline_days' => $input['repair_deadline_days'] ?? config('surveillance.vms.failure_repair_deadline_days.'.$category->value),
            'operation_id' => $input['operation_id'] ?? null,
        ]);

        $context->publish(
            vessel: $failure->vessel_id,
            module: 'vms',
            type: 'failure.declared',
            subject: $failure,
            summaryKey: 'vms::events.failure_declared',
            params: ['days' => $failure->repair_deadline_days],
            occurredAt: $failure->started_at,
        );

        return $failure;
    }
}
