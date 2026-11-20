<?php

namespace App\Models;

use App\Enums\LogbookDirection;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['operation_id', 'occurred_at', 'channel_id', 'direction', 'body', 'is_order_or_decision', 'entered_by'])]
class OperationEvent extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'direction' => LogbookDirection::class,
            'is_order_or_decision' => 'boolean',
        ];
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ReferenceListValue::class, 'channel_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
