<?php

namespace App\Models;

use App\Enums\AuthorizingRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mission_id', 'authorized_by', 'authorizing_role', 'authorized_at'])]
class MissionAuthorization extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'authorizing_role' => AuthorizingRole::class,
            'authorized_at' => 'datetime',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
