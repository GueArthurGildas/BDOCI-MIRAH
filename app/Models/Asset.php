<?php

namespace App\Models;

use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['type', 'label', 'is_active'])]
class Asset extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => AssetType::class,
            'is_active' => 'boolean',
        ];
    }

    public function missionAssets(): HasMany
    {
        return $this->hasMany(MissionAsset::class);
    }
}
