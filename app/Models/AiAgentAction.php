<?php

namespace App\Models;

use App\Enums\AiAgentActionStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['action_id', 'requested_by', 'instruction', 'parameters', 'status', 'executed_at', 'result_object_type', 'result_object_id'])]
class AiAgentAction extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'status' => AiAgentActionStatus::class,
            'executed_at' => 'datetime',
        ];
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(ActionCatalogEntry::class, 'action_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function result(): MorphTo
    {
        return $this->morphTo('result', 'result_object_type', 'result_object_id');
    }
}
