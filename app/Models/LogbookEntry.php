<?php

namespace App\Models;

use App\Enums\LogbookDirection;
use App\Enums\LogbookHandlingStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['logbook_day_id', 'occurred_at', 'channel_id', 'content', 'direction', 'is_order_or_decision', 'operation_id', 'handling_status', 'corrects_entry_id', 'entered_by'])]
class LogbookEntry extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'direction' => LogbookDirection::class,
            'handling_status' => LogbookHandlingStatus::class,
            'is_order_or_decision' => 'boolean',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(LogbookDay::class, 'logbook_day_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ReferenceListValue::class, 'channel_id');
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_id');
    }

    public function corrects(): BelongsTo
    {
        return $this->belongsTo(LogbookEntry::class, 'corrects_entry_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(LogbookEntry::class, 'corrects_entry_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function vessels(): BelongsToMany
    {
        return $this->belongsToMany(Vessel::class, 'logbook_entry_vessel');
    }
}
