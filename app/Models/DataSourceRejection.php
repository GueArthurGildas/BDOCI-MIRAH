<?php

namespace App\Models;

use App\Enums\RejectionReason;
use App\Enums\RejectionStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['data_source_id', 'data_source_sync_id', 'external_ref', 'reason', 'details', 'payload', 'status', 'received_at', 'handled_at', 'handled_by'])]
class DataSourceRejection extends Model
{
    use Audited, HasFactory;

    protected static $recordEvents = ['updated'];

    protected function casts(): array
    {
        return [
            'reason' => RejectionReason::class,
            'status' => RejectionStatus::class,
            'payload' => 'array',
            'received_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }

    public function sync(): BelongsTo
    {
        return $this->belongsTo(DataSourceSync::class, 'data_source_sync_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
