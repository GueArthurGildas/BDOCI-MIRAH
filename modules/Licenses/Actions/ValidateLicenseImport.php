<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseImportLineStatus;
use App\Models\LicenseImport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;
use Modules\Licenses\Support\ImportCompletion;

class ValidateLicenseImport extends Action
{
    public function __construct(
        private readonly ValidateLicenseImportLine $lineValidation,
        private readonly ImportCompletion $completion,
    ) {}

    public function code(): string
    {
        return 'licenses.validate_import';
    }

    public function permission(): Permission
    {
        return Permission::LicensesValidate;
    }

    public function handle(ActionContext $context, array $input): LicenseImport
    {
        $import = LicenseImport::lockForUpdate()->findOrFail($input['import_id']);

        if (! $import->isOpen()) {
            throw ValidationException::withMessages(['import' => __('licenses::imports.errors.import_closed')]);
        }

        $import->lines()->where('status', LicenseImportLineStatus::ToValidate->value)->get()->each(function ($line) use ($context, $import) {
            $line->setRelation('licenseImport', $import);
            $this->lineValidation->process($context, $line);
        });

        $this->completion->closeIfDone($import);

        return $import;
    }
}
