<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['alert_id', 'verification_questions', 'is_vessel_contacted', 'measures', 'handled_by', 'handled_at'])]
class AlertHandling extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'verification_questions' => 'array',
            'is_vessel_contacted' => 'boolean',
            'handled_at' => 'datetime',
        ];
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
