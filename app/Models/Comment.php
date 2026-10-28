<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['object_type', 'object_id', 'user_id', 'kind', 'body'])]
class Comment extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    public function object(): MorphTo
    {
        return $this->morphTo('object');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
