<?php

namespace Modules\Licenses\Actions;

use App\Models\VmsCompatibilityCertificate;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class RecordVmsCertificate extends Action
{
    public function code(): string
    {
        return 'licenses.record_vms_certificate';
    }

    public function permission(): Permission
    {
        return Permission::LicensesManage;
    }

    public function handle(ActionContext $context, array $input): VmsCompatibilityCertificate
    {
        $certificate = VmsCompatibilityCertificate::create(Arr::only($input, ['vessel_id', 'issued_on', 'expires_on']));

        $context->publish(
            vessel: $certificate->vessel_id,
            module: 'licenses',
            type: 'vms_certificate.recorded',
            subject: $certificate,
            summaryKey: 'licenses::events.vms_certificate_recorded',
            params: ['expires_on' => $certificate->expires_on->toDateString()],
        );

        return $certificate;
    }
}
