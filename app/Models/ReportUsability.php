<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['witness_report_id', 'is_usable', 'interview_report'])]
class ReportUsability extends Model
{
    use HasFactory;

    protected $primaryKey = 'witness_report_id';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_usable' => 'boolean',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(WitnessReport::class, 'witness_report_id');
    }
}
