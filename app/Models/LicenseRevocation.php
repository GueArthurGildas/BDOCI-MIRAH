<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['license_id', 'reason', 'revoked_on', 'decided_by', 'infraction_case_id', 'is_repeat_offense'])]
class LicenseRevocation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'revoked_on' => 'date',
            'is_repeat_offense' => 'boolean',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
