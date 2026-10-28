<?php

namespace App\Models;

use App\Enums\LogbookDayStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['day', 'shift_start_at', 'shift_end_at', 'status', 'pdf_document_id', 'locked_at'])]
class LogbookDay extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'day' => 'date',
            'shift_start_at' => 'datetime',
            'shift_end_at' => 'datetime',
            'locked_at' => 'datetime',
            'status' => LogbookDayStatus::class,
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LogbookEntry::class, 'logbook_day_id')->orderBy('occurred_at')->orderBy('id');
    }

    public function closing(): HasOne
    {
        return $this->hasOne(LogbookClosing::class, 'logbook_day_id');
    }

    public function pdfDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'pdf_document_id');
    }
}
