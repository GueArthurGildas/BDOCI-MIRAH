<?php

namespace App\Models;

use App\Enums\OperationStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['code', 'category_id', 'number', 'year', 'name', 'status', 'started_at', 'ended_at', 'origin', 'summary', 'result', 'created_by', 'closed_by'])]
class Operation extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OperationStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(OperationCategory::class, 'category_id');
    }

    public function context(): HasOne
    {
        return $this->hasOne(OperationContext::class, 'operation_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OperationEvent::class, 'operation_id')->orderBy('occurred_at');
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(OperationInstruction::class, 'operation_id')->latest('id');
    }

    public function logbookEntries(): HasMany
    {
        return $this->hasMany(LogbookEntry::class, 'operation_id')->orderBy('occurred_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function vessels(): BelongsToMany
    {
        return $this->belongsToMany(Vessel::class, 'operation_vessel');
    }

    public function personnel(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'operation_user')->withPivot('role');
    }
}
