<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'locality', 'latitude', 'longitude', 'zone_id', 'is_designated'])]
class Port extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Port $port) {
            $port->code ??= strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $port->locality ?? $port->name), 0, 3)).strtoupper(str()->random(4));
        });
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_designated' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
