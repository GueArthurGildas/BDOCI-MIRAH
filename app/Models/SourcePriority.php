<?php

namespace App\Models;

use App\Enums\SourceSystem;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['field', 'source', 'rank'])]
class SourcePriority extends Model
{
    protected $table = 'source_priorities';

    public $incrementing = false;

    protected $primaryKey = null;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'source' => SourceSystem::class,
            'rank' => 'integer',
        ];
    }
}
