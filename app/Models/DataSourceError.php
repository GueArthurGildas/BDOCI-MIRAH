<?php

namespace App\Models;

use App\Enums\ConnectorErrorCode;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['data_source_id', 'data_source_sync_id', 'occurred_at', 'code', 'message', 'resolved_at', 'resolved_by'])]
class DataSourceError extends Model
{
    use Audited, HasFactory;

    protected static $recordEvents = ['updated'];

    protected function casts(): array
    {
        return [
            'code' => ConnectorErrorCode::class,
            'occurred_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }

    public function sync(): BelongsTo
    {
        return $this->belongsTo(DataSourceSync::class, 'data_source_sync_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
