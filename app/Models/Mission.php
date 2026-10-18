<?php

namespace App\Models;

use App\Enums\MissionStatus;
use App\Enums\MissionType;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['reference', 'zone_id', 'type', 'objective', 'starts_at', 'ends_at', 'status', 'results'])]
class Mission extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'type' => MissionType::class,
            'status' => MissionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function vessels(): BelongsToMany
    {
        return $this->belongsToMany(Vessel::class);
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
