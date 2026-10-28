<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['new_beacon_request_id', 'installer_id', 'installed_on', 'report_document_id'])]
class BeaconInstallation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'installed_on' => 'date',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(NewBeaconRequest::class, 'new_beacon_request_id');
    }

    public function installer(): BelongsTo
    {
        return $this->belongsTo(ApprovedInstaller::class, 'installer_id');
    }

    public function reportDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'report_document_id');
    }
}
