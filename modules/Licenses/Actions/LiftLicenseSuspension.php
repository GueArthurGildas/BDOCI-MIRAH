<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseRecordStatus;
use App\Models\License;
use App\Models\LicenseSuspension;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class LiftLicenseSuspension extends Action
{
    public function code(): string
    {
        return 'licenses.lift_suspension';
    }

    public function permission(): Permission
    {
        return Permission::LicensesSuspend;
    }

    public function handle(ActionContext $context, array $input): LicenseSuspension
    {
        $suspension = LicenseSuspension::lockForUpdate()->findOrFail($input['suspension_id']);
        $liftedOn = Carbon::parse($input['lifted_on'] ?? today());

        if (! $suspension->isInForce()) {
            throw ValidationException::withMessages(['suspension' => __('licenses::licenses.errors.suspension_already_lifted')]);
        }

        if ($liftedOn->lt($suspension->started_on)) {
            throw ValidationException::withMessages(['lifted_on' => __('licenses::licenses.errors.suspension.end_before_start')]);
        }

        $suspension->update(['lifted_on' => $liftedOn]);

        $license = License::lockForUpdate()->findOrFail($suspension->license_id);

        if ($license->status === LicenseRecordStatus::Suspended && ! $license->suspensions()->inForce()->exists()) {
            $license->update(['status' => LicenseRecordStatus::Active]);
        }

        $context->publish(
            vessel: $license->vessel_id,
            module: 'licenses',
            type: 'license.suspension_lifted',
            subject: $suspension,
            summaryKey: 'licenses::events.suspension_lifted',
            params: ['number' => $license->number, 'lifted_on' => $liftedOn->toDateString()],
        );

        return $suspension;
    }
}
