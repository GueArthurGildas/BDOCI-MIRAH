<?php

namespace App\Models;

use App\Enums\EezDeclarationType;
use App\Enums\EntryExitAlertStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'eez_declaration_id', 'direction', 'alerted_at', 'status'])]
class EntryExitAlert extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'direction' => EezDeclarationType::class,
            'status' => EntryExitAlertStatus::class,
            'alerted_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(EezDeclaration::class, 'eez_declaration_id');
    }
}
