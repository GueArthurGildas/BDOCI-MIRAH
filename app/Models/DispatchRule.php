<?php

namespace App\Models;

use App\Enums\DispatchMode;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['message_template_id', 'trigger_event', 'conditions', 'frequency_minutes', 'daily_cap', 'stop_condition', 'mode', 'is_active', 'editable_by_permission_group_id'])]
class DispatchRule extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'mode' => DispatchMode::class,
            'is_active' => 'boolean',
            'frequency_minutes' => 'integer',
            'daily_cap' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    public function editableByGroup(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'editable_by_permission_group_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SentMessage::class);
    }
}
