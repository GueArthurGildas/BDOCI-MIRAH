<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['module_id', 'code', 'verb', 'ai_agent_allowed', 'ai_agent_enabled'])]
class ActionCatalogEntry extends Model
{
    protected $table = 'action_catalog';

    protected function casts(): array
    {
        return ['ai_agent_allowed' => 'boolean', 'ai_agent_enabled' => 'boolean'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(RegisteredModule::class, 'module_id');
    }
}
