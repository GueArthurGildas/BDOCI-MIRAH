<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'mime_type', 'path', 'sha256_fingerprint', 'is_frozen', 'filed_by'])]
class Document extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['is_frozen' => 'boolean'];
    }

    public function filer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }
}
