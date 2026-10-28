<?php

namespace Modules\Licenses\Actions;

use App\Models\LicenseImport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class StartLicenseBatch extends Action
{
    public function code(): string
    {
        return 'licenses.start_batch';
    }

    public function permission(): Permission
    {
        return Permission::LicensesImport;
    }

    public function handle(ActionContext $context, array $input): LicenseImport
    {
        $import = LicenseImport::create(['created_by' => $context->actor->id]);

        foreach (array_unique($input['vessel_ids']) as $vesselId) {
            $import->lines()->create(['vessel_id' => $vesselId, 'data' => ['number' => '', 'segment' => '', 'issued_on' => '', 'expires_on' => '', 'zones' => [], 'gears' => [], 'species' => [], 'conditions' => []]]);
        }

        return $import;
    }
}
