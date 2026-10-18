<?php

namespace App\Models;

use App\Enums\DataSourceKind;
use App\Enums\DataSourceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'kind', 'status', 'last_synced_at', 'error_count', 'rejected_count'])]
class DataSource extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'kind' => DataSourceKind::class,
            'status' => DataSourceStatus::class,
            'last_synced_at' => 'datetime',
        ];
    }
}
