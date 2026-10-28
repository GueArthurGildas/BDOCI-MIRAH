<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dpep_id', 'recipient', 'channel', 'notified_at', 'status'])]
class MrepNotification extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
        ];
    }

    public function dpep(): BelongsTo
    {
        return $this->belongsTo(MrepDpep::class, 'dpep_id');
    }
}
