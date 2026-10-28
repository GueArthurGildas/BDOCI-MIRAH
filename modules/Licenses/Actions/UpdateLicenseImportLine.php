<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseImportLineStatus;
use App\Models\LicenseImportLine;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;
use Modules\Licenses\Support\LineChecker;

class UpdateLicenseImportLine extends Action
{
    public function __construct(private readonly LineChecker $checker) {}

    public function code(): string
    {
        return 'licenses.update_import_line';
    }

    public function permission(): Permission
    {
        return Permission::LicensesImport;
    }

    public function handle(ActionContext $context, array $input): LicenseImportLine
    {
        $line = LicenseImportLine::lockForUpdate()->with('licenseImport')->findOrFail($input['line_id']);

        if ($line->status === LicenseImportLineStatus::Valid || ! $line->licenseImport->isOpen()) {
            throw ValidationException::withMessages(['line' => __('licenses::imports.errors.line_closed')]);
        }

        $data = [...$line->data, ...$input['data']];
        $rejection = $line->status === LicenseImportLineStatus::Rejected ? $this->checker->rejection($data, $line->vessel_id) : null;

        $line->update([
            'data' => $data,
            'status' => $rejection ? LicenseImportLineStatus::Rejected : LicenseImportLineStatus::ToValidate,
            'rejection_reason' => $rejection,
        ]);

        return $line;
    }
}
