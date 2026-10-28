<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseImportStatus;
use App\Models\LicenseImport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class CancelLicenseImport extends Action
{
    public function code(): string
    {
        return 'licenses.cancel_import';
    }

    public function permission(): Permission
    {
        return Permission::LicensesImport;
    }

    public function handle(ActionContext $context, array $input): LicenseImport
    {
        $import = LicenseImport::lockForUpdate()->findOrFail($input['import_id']);

        if (! $import->isOpen()) {
            throw ValidationException::withMessages(['import' => __('licenses::imports.errors.import_closed')]);
        }

        $import->update(['status' => LicenseImportStatus::Cancelled]);

        return $import;
    }
}
