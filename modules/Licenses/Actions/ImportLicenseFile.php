<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseImportLineStatus;
use App\Models\Document;
use App\Models\LicenseImport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Licenses\Support\CsvReader;
use Modules\Licenses\Support\LineChecker;

class ImportLicenseFile extends Action
{
    public function __construct(
        private readonly CsvReader $reader,
        private readonly LineChecker $checker,
    ) {}

    public function code(): string
    {
        return 'licenses.import_file';
    }

    public function permission(): Permission
    {
        return Permission::LicensesImport;
    }

    public function handle(ActionContext $context, array $input): LicenseImport
    {
        $document = Document::findOrFail($input['document_id']);
        $rows = $this->reader->read(Storage::disk(DocumentStore::DISK)->get($document->path));

        if ($rows === []) {
            throw ValidationException::withMessages(['file' => __('licenses::imports.errors.empty_file')]);
        }

        $import = LicenseImport::create([
            'document_id' => $document->id,
            'created_by' => $context->actor->id,
        ]);

        $numbers = [];

        foreach ($rows as $row) {
            $rejection = $this->checker->rejection($row['data'], $row['vessel_id'], $numbers);
            $numbers[] = $row['data']['number'];

            $import->lines()->create([
                'vessel_id' => $row['vessel_id'],
                'data' => $row['data'],
                'status' => $rejection ? LicenseImportLineStatus::Rejected : LicenseImportLineStatus::ToValidate,
                'rejection_reason' => $rejection,
            ]);
        }

        return $import;
    }
}
