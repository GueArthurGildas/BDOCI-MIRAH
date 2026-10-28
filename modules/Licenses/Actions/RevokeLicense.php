<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseRecordStatus;
use App\Models\License;
use App\Models\LicenseRevocation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class RevokeLicense extends Action
{
    public function code(): string
    {
        return 'licenses.revoke';
    }

    public function permission(): Permission
    {
        return Permission::LicensesRevoke;
    }

    public function handle(ActionContext $context, array $input): LicenseRevocation
    {
        $license = License::lockForUpdate()->findOrFail($input['license_id']);

        if ($license->status === LicenseRecordStatus::Revoked) {
            throw ValidationException::withMessages(['license' => __('licenses::licenses.errors.license_revoked')]);
        }

        $revocation = $license->revocation()->create([
            'reason' => $input['reason'],
            'revoked_on' => $input['revoked_on'],
            'decided_by' => $context->actor->id,
            'infraction_case_id' => $input['infraction_case_id'] ?? null,
            'is_repeat_offense' => (bool) ($input['is_repeat_offense'] ?? false),
        ]);

        $license->update(['status' => LicenseRecordStatus::Revoked]);

        $context->publish(
            vessel: $license->vessel_id,
            module: 'licenses',
            type: 'license.revoked',
            subject: $revocation,
            summaryKey: 'licenses::events.revoked',
            params: ['number' => $license->number, 'revoked_on' => $revocation->revoked_on->toDateString()],
        );

        return $revocation;
    }
}
