<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseRecordStatus;
use App\Enums\SuspensionReason;
use App\Models\License;
use App\Models\LicenseSuspension;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Licenses\Support\SuspensionRules;

class SuspendLicense extends Action
{
    public function code(): string
    {
        return 'licenses.suspend';
    }

    public function permission(): Permission
    {
        return Permission::LicensesSuspend;
    }

    public function handle(ActionContext $context, array $input): LicenseSuspension
    {
        $license = License::lockForUpdate()->findOrFail($input['license_id']);
        $reason = SuspensionReason::from($input['reason']);
        $startedOn = Carbon::parse($input['started_on']);
        $plannedEndOn = filled($input['planned_end_on'] ?? null) ? Carbon::parse($input['planned_end_on']) : null;

        if ($license->status === LicenseRecordStatus::Revoked) {
            throw ValidationException::withMessages(['license' => __('licenses::licenses.errors.license_revoked')]);
        }

        if ($violation = SuspensionRules::violation($reason, $startedOn, $plannedEndOn)) {
            throw ValidationException::withMessages(['planned_end_on' => __("licenses::licenses.errors.suspension.{$violation}", ['months' => config('surveillance.license_suspension_max_months')])]);
        }

        $suspension = $license->suspensions()->create([
            ...Arr::only($input, ['infraction_case_id', 'vms_failure_id', 'operation_id', 'proposed_by']),
            'reason' => $reason,
            'started_on' => $startedOn,
            'planned_end_on' => $plannedEndOn,
            'decided_by' => $context->actor->id,
        ]);

        $license->update(['status' => LicenseRecordStatus::Suspended]);

        $context->publish(
            vessel: $license->vessel_id,
            module: 'licenses',
            type: 'license.suspended',
            subject: $suspension,
            summaryKey: 'licenses::events.suspended.'.$reason->value,
            params: ['number' => $license->number, 'started_on' => $startedOn->toDateString()],
        );

        return $suspension;
    }
}
