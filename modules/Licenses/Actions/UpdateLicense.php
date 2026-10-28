<?php

namespace Modules\Licenses\Actions;

use App\Models\License;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;
use Modules\Licenses\Support\LicenseWriter;

class UpdateLicense extends Action
{
    public function __construct(private readonly LicenseWriter $writer) {}

    public function code(): string
    {
        return 'licenses.update';
    }

    public function permission(): Permission
    {
        return Permission::LicensesManage;
    }

    public function handle(ActionContext $context, array $input): License
    {
        $license = License::lockForUpdate()->findOrFail($input['license_id']);

        $license->update(Arr::only($input, ['number', 'segment', 'issued_on', 'expires_on']));
        $this->writer->syncDetails($license, $input);

        $context->publish(
            vessel: $license->vessel_id,
            module: 'licenses',
            type: 'license.updated',
            subject: $license,
            summaryKey: 'licenses::events.updated',
            params: ['number' => $license->number, 'expires_on' => $license->expires_on->toDateString()],
        );

        return $license;
    }
}
