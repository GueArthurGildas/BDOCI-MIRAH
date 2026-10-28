<?php

namespace App\Models;

use App\Enums\LicenseImportLineStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['license_import_id', 'vessel_id', 'data', 'status', 'rejection_reason', 'license_id'])]
class LicenseImportLine extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'status' => LicenseImportLineStatus::class,
        ];
    }

    public function licenseImport(): BelongsTo
    {
        return $this->belongsTo(LicenseImport::class);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
