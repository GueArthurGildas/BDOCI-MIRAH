<?php

namespace App\Models;

use App\Enums\ActorRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'role', 'legal_entity_id', 'person_id', 'started_on', 'ended_on'])]
class VesselActor extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'role' => ActorRole::class,
            'started_on' => 'date',
            'ended_on' => 'date',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function holderName(): ?string
    {
        return $this->legalEntity?->name ?? trim(($this->person?->first_name ?? '').' '.($this->person?->name ?? '')) ?: null;
    }
}
