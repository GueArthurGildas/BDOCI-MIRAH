<?php

namespace App\Models;

use App\Enums\NoteRelay;
use App\Enums\NoteType;
use App\Enums\NoteUrgency;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'type', 'vessel_id', 'urgency', 'recipient_user_id', 'recipient_team_id', 'due_date', 'free_text',
    'author_id', 'ai_agent_action_id', 'is_read', 'read_at', 'relayed_by', 'logbook_entry_id',
])]
class InternalNote extends Model
{
    use Audited, HasFactory;

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type' => NoteType::class,
            'urgency' => NoteUrgency::class,
            'relayed_by' => NoteRelay::class,
            'due_date' => 'datetime',
            'read_at' => 'datetime',
            'is_read' => 'boolean',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function recipientTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'recipient_team_id');
    }

    public function aiAgentAction(): BelongsTo
    {
        return $this->belongsTo(AiAgentAction::class);
    }

    public function scopeAddressedTo(Builder $query, User $user): void
    {
        $query->where(fn (Builder $query) => $query
            ->where('recipient_user_id', $user->id)
            ->when($user->team_id, fn (Builder $query) => $query->orWhere('recipient_team_id', $user->team_id)));
    }

    public function isAddressedTo(User $user): bool
    {
        return $this->recipient_user_id === $user->id
            || ($this->recipient_team_id !== null && $this->recipient_team_id === $user->team_id);
    }

    public function isOverdue(): bool
    {
        return ! $this->is_read && $this->due_date !== null && $this->due_date->isPast();
    }
}
