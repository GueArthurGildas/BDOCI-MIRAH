<?php

namespace App\Models;

use App\Enums\ExportFormat;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'subject', 'permission', 'format', 'filters', 'row_count', 'exported_at'])]
class ExportLog extends Model
{
    use HasFactory;

    protected $table = 'export_log';

    protected function casts(): array
    {
        return [
            'format' => ExportFormat::class,
            'filters' => 'array',
            'row_count' => 'integer',
            'exported_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
