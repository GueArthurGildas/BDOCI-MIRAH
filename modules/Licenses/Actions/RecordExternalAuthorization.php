<?php

namespace Modules\Licenses\Actions;

use App\Models\ExternalAuthorization;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class RecordExternalAuthorization extends Action
{
    public function code(): string
    {
        return 'licenses.record_external_authorization';
    }

    public function permission(): Permission
    {
        return Permission::LicensesManage;
    }

    public function handle(ActionContext $context, array $input): ExternalAuthorization
    {
        $authorization = ExternalAuthorization::create(Arr::only($input, ['vessel_id', 'institution_id', 'number', 'valid_from', 'valid_to']));

        $context->publish(
            vessel: $authorization->vessel_id,
            module: 'licenses',
            type: 'external_authorization.recorded',
            subject: $authorization,
            summaryKey: 'licenses::events.external_authorization_recorded',
            params: ['institution' => $authorization->institution->name, 'valid_to' => $authorization->valid_to->toDateString()],
        );

        return $authorization;
    }
}
