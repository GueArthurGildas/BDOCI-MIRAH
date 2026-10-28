<?php

namespace App\Models;

use App\Enums\EezDeclarationStatus;
use App\Enums\EezDeclarationType;
use App\Enums\EezEntryOrigin;
use App\Enums\EezReason;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'vessel_id', 'type', 'received_at', 'reception_channel_id', 'entry_origin', 'entered_by', 'crossed_at',
    'latitude', 'longitude', 'route', 'speed', 'destination', 'reason', 'declared_name', 'declared_imo',
    'declared_mmsi', 'declared_ircs', 'declared_flag', 'declared_owner', 'status',
])]
class EezDeclaration extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'type' => EezDeclarationType::class,
            'entry_origin' => EezEntryOrigin::class,
            'reason' => EezReason::class,
            'status' => EezDeclarationStatus::class,
            'received_at' => 'datetime',
            'crossed_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'speed' => 'float',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function receptionChannel(): BelongsTo
    {
        return $this->belongsTo(ReferenceListValue::class, 'reception_channel_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function catches(): HasMany
    {
        return $this->hasMany(EezDeclarationCatch::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(EezDeclarationCheck::class)->orderBy('id');
    }

    public function alert(): HasOne
    {
        return $this->hasOne(EntryExitAlert::class);
    }

    public function linkAsEntry(): HasOne
    {
        return $this->hasOne(EntryExitLink::class, 'entry_declaration_id');
    }

    public function linkAsExit(): HasOne
    {
        return $this->hasOne(EntryExitLink::class, 'exit_declaration_id');
    }

    public function totalCatchKg(): float
    {
        return (float) $this->catches->sum('weight_kg');
    }

    public function displayName(): string
    {
        return $this->vessel?->name ?? $this->declared_name ?? '—';
    }
}
