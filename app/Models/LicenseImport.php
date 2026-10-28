<?php

namespace App\Models;

use App\Enums\LicenseImportStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_id', 'status', 'created_by', 'validated_at'])]
class LicenseImport extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => LicenseImportStatus::class,
            'validated_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(LicenseImportLine::class)->orderBy('id');
    }

    public function isOpen(): bool
    {
        return $this->status === LicenseImportStatus::Preview;
    }
}
