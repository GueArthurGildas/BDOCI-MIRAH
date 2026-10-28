<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vms_failure_id', 'ordered_at', 'includes_suspension', 'requires_port_return', 'given_by'])]
class CessationOrder extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'includes_suspension' => 'boolean',
            'requires_port_return' => 'boolean',
        ];
    }

    public function failure(): BelongsTo
    {
        return $this->belongsTo(VmsFailure::class, 'vms_failure_id');
    }

    public function giver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'given_by');
    }
}
