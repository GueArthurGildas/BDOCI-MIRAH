<?php

namespace Modules\Licenses\Support;

use App\Enums\LicenseImportLineStatus;
use App\Enums\LicenseImportStatus;
use App\Models\LicenseImport;

class ImportCompletion
{
    public function closeIfDone(LicenseImport $import): void
    {
        if ($import->lines()->where('status', LicenseImportLineStatus::ToValidate->value)->doesntExist()) {
            $import->update(['status' => LicenseImportStatus::Valid, 'validated_at' => now()]);
        }
    }
}
