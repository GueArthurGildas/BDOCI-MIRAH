<?php

namespace App\Models;

use App\Enums\ReportFormat;
use App\Enums\ReportFrequency;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'frequency', 'format', 'recipient_id', 'is_active'])]
class ScheduledReport extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'frequency' => ReportFrequency::class,
            'format' => ReportFormat::class,
            'is_active' => 'boolean',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
