<?php

namespace Modules\Reports\Support;

use App\Enums\OperationStatus;
use App\Models\Operation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ArchivableOperations
{
    public function cutoff(): Carbon
    {
        return now()->subYears(config('surveillance.archive_after_years'));
    }

    /** @return Builder<Operation> */
    public function query(): Builder
    {
        return Operation::query()
            ->where('status', OperationStatus::Closed->value)
            ->whereNotNull('ended_at')
            ->where('ended_at', '<=', $this->cutoff());
    }

    public function isEligible(Operation $operation): bool
    {
        return $operation->status === OperationStatus::Closed
            && $operation->ended_at !== null
            && $operation->ended_at->lte($this->cutoff());
    }
}
