<?php

namespace App\Support\Logbook;

use App\Enums\OperationCategoryCode;
use App\Enums\OperationStatus;
use App\Models\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class OperationSnapshot
{
    /** @return array<string, int> */
    public function lastNumbers(int $year): array
    {
        $numbers = DB::table('operation_counters')
            ->join('operation_categories', 'operation_categories.id', '=', 'operation_counters.category_id')
            ->where('operation_counters.year', $year)
            ->pluck('operation_counters.last_number', 'operation_categories.code');

        return $this->perCategory(fn (string $code) => (int) ($numbers[$code] ?? 0));
    }

    /** @return array<string, array<int, string>> */
    public function openOperations(): array
    {
        $operations = Operation::query()
            ->whereIn('status', [OperationStatus::Open, OperationStatus::InProgress])
            ->with('category')
            ->orderBy('year')
            ->orderBy('number')
            ->get()
            ->groupBy(fn (Operation $operation) => $operation->category->code->value);

        return $this->perCategory(fn (string $code) => $operations->get($code, new Collection)->map(fn (Operation $operation) => $operation->code)->values()->all());
    }

    /** @return array<string, int> */
    public function openedBetween(Carbon $from, Carbon $to): array
    {
        return $this->countBetween('started_at', $from, $to);
    }

    /** @return array<string, int> */
    public function closedBetween(Carbon $from, Carbon $to): array
    {
        return $this->countBetween('ended_at', $from, $to);
    }

    /** @return array<string, int> */
    private function countBetween(string $column, Carbon $from, Carbon $to): array
    {
        $counts = Operation::query()
            ->where($column, '>=', $from)
            ->where($column, '<', $to)
            ->with('category')
            ->get()
            ->countBy(fn (Operation $operation) => $operation->category->code->value);

        return $this->perCategory(fn (string $code) => (int) ($counts[$code] ?? 0));
    }

    /**
     * @template T
     *
     * @param  callable(string): T  $value
     * @return array<string, T>
     */
    private function perCategory(callable $value): array
    {
        return collect(OperationCategoryCode::cases())->mapWithKeys(fn (OperationCategoryCode $code) => [$code->value => $value($code->value)])->all();
    }
}
