<?php

namespace App\Models;

use App\Enums\MessageStatus;
use App\Enums\MessageTrigger;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'message_template_id', 'dispatch_rule_id', 'communication_channel_id', 'vessel_id', 'operation_id',
    'logbook_entry_id', 'recipient', 'subject', 'body', 'status', 'sent_at', 'acknowledged_at',
    'triggered_by', 'user_id', 'reply_received',
])]
class SentMessage extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'status' => MessageStatus::class,
            'triggered_by' => MessageTrigger::class,
            'sent_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'reply_received' => 'boolean',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(DispatchRule::class, 'dispatch_rule_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(CommunicationChannel::class, 'communication_channel_id');
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAwaitingAcknowledgment(Builder $query): void
    {
        $query->where('status', MessageStatus::Sent->value);
    }
}
