<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['role_id', 'permission_group_id', 'action_id', 'zone_id', 'valid_from', 'valid_to'])]
class PermissionGrant extends Model
{
    protected function casts(): array
    {
        return ['valid_from' => 'date', 'valid_to' => 'date'];
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(ActionCatalogEntry::class, 'action_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
