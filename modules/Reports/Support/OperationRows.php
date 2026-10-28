<?php

namespace Modules\Reports\Support;

use App\Models\Operation;

final class OperationRows
{
    /** @return array<int, string> */
    public static function headings(): array
    {
        return collect(['code', 'name', 'category', 'status', 'started_at', 'ended_at'])
            ->map(fn (string $column) => __("reports::reports.columns.{$column}"))
            ->all();
    }

    /** @return array<int, scalar|null> */
    public static function row(Operation $operation): array
    {
        return [
            $operation->code,
            $operation->name,
            $operation->category ? __($operation->category->code->label()) : null,
            __($operation->status->label()),
            $operation->started_at->format('Y-m-d H:i'),
            $operation->ended_at?->format('Y-m-d H:i'),
        ];
    }
}
