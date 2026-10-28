<?php

namespace App\Models;

use App\Enums\AlertRuleType;
use App\Enums\AlertStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vessel_id', 'alert_rule_id', 'type', 'zone_id', 'triggered_at', 'status', 'acknowledged_by', 'logbook_entry_id', 'operation_id'])]
class Alert extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'type' => AlertRuleType::class,
            'triggered_at' => 'datetime',
            'status' => AlertStatus::class,
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'alert_rule_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function logbookEntry(): BelongsTo
    {
        return $this->belongsTo(LogbookEntry::class);
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function handlings(): HasMany
    {
        return $this->hasMany(AlertHandling::class);
    }

    public function scopeUnresolved(Builder $query): void
    {
        $query->whereIn('status', [AlertStatus::Open, AlertStatus::Acknowledged]);
    }
}
