<?php

namespace App\Models;

use App\Enums\ErsSource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vessel_id', 'declared_at', 'source'])]
class ErsDeclaration extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'declared_at' => 'datetime',
            'source' => ErsSource::class,
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ErsCatchLine::class, 'ers_declaration_id');
    }
}
