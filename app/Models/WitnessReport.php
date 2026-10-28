<?php

namespace App\Models;

use App\Enums\ReportChannel;
use App\Enums\WitnessReportStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['received_at', 'witness_id', 'latitude', 'longitude', 'channel', 'operation_id', 'entered_by', 'status'])]
class WitnessReport extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'channel' => ReportChannel::class,
            'status' => WitnessReportStatus::class,
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ];
    }

    public function witness(): BelongsTo
    {
        return $this->belongsTo(Witness::class);
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function reportedVessels(): HasMany
    {
        return $this->hasMany(ReportedVessel::class);
    }

    public function usability(): HasOne
    {
        return $this->hasOne(ReportUsability::class);
    }

    public function investigation(): HasOne
    {
        return $this->hasOne(ReportInvestigation::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(ReportDecision::class);
    }

    public function infractionCases(): HasMany
    {
        return $this->hasMany(InfractionCase::class);
    }
}
