<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['name', 'mime_type', 'disk', 'path', 'sha256', 'is_frozen', 'uploaded_by'])]
class Document extends Model
{
    protected function casts(): array
    {
        return ['is_frozen' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::updating(function (self $document) {
            throw_if($document->getOriginal('is_frozen'), LogicException::class, 'A frozen document cannot be modified.');
        });

        static::deleting(function (self $document) {
            throw_if($document->is_frozen, LogicException::class, 'A frozen document cannot be deleted.');
        });
    }

    public function freeze(): void
    {
        $this->is_frozen = true;
        $this->save();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
