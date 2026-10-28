<?php

namespace App\Models;

use App\Enums\FnpDeregistrationReason;
use App\Enums\FnpRegistration;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'status', 'deregistration_reason', 'status_on'])]
class FnpStatus extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'vessel_id';

    protected function casts(): array
    {
        return [
            'status' => FnpRegistration::class,
            'deregistration_reason' => FnpDeregistrationReason::class,
            'status_on' => 'date',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
