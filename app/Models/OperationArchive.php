<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['operation_id', 'archived_at'])]
class OperationArchive extends Model
{
    use HasFactory;

    protected $primaryKey = 'operation_id';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_id');
    }
}
