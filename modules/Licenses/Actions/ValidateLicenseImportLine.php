<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseImportLineStatus;
use App\Enums\LicenseOrigin;
use App\Models\LicenseImportLine;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;
use Modules\Licenses\Support\ImportCompletion;
use Modules\Licenses\Support\LicenseWriter;
use Modules\Licenses\Support\LineChecker;

class ValidateLicenseImportLine extends Action
{
    public function __construct(
        private readonly LineChecker $checker,
        private readonly LicenseWriter $writer,
        private readonly ImportCompletion $completion,
    ) {}

    public function code(): string
    {
        return 'licenses.validate_import_line';
    }

    public function permission(): Permission
    {
        return Permission::LicensesValidate;
    }

    public function handle(ActionContext $context, array $input): LicenseImportLine
    {
        $line = LicenseImportLine::lockForUpdate()->with('licenseImport')->findOrFail($input['line_id']);

        if ($line->status === LicenseImportLineStatus::Valid || ! $line->licenseImport->isOpen()) {
            throw ValidationException::withMessages(['line' => __('licenses::imports.errors.line_closed')]);
        }

        if (isset($input['data'])) {
            $line->data = [...$line->data, ...$input['data']];
        }

        $this->process($context, $line);
        $this->completion->closeIfDone($line->licenseImport);

        return $line;
    }

    public function process(ActionContext $context, LicenseImportLine $line): void
    {
        $rejection = $this->checker->rejection($line->data, $line->vessel_id);

        if ($rejection) {
            $line->update(['status' => LicenseImportLineStatus::Rejected, 'rejection_reason' => $rejection]);

            return;
        }

        $license = $this->writer->create($context, $this->checker->licenseInput($line->data, $line->vessel_id), $line->licenseImport->document_id ? LicenseOrigin::Import : LicenseOrigin::Batch);
        $line->update(['status' => LicenseImportLineStatus::Valid, 'rejection_reason' => null, 'license_id' => $license->id]);
    }
}
