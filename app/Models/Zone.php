<?php

namespace App\Models;

use App\Enums\ZoneType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'geometry', 'is_active'])]
class Zone extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => ZoneType::class,
            'geometry' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function ports(): HasMany
    {
        return $this->hasMany(Port::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
