<?php

namespace Modules\Reports\Support;

use App\Models\LogbookEntry;
use App\Models\Operation;
use InvalidArgumentException;

class ReportCatalog
{
    public const OPERATIONS_ACTIVITY = 'operations_activity';

    public const LOGBOOK_ACTIVITY = 'logbook_activity';

    /** @return array<int, string> */
    public function codes(): array
    {
        return [self::OPERATIONS_ACTIVITY, self::LOGBOOK_ACTIVITY];
    }

    /** @return array<string, string> */
    public function options(): array
    {
        return collect($this->codes())->mapWithKeys(fn (string $code) => [$code => $this->label($code)])->all();
    }

    public function label(string $code): string
    {
        return __("reports::reports.catalog.{$code}");
    }

    public function build(string $code, ReportPeriod $period): ExportDataset
    {
        return match ($code) {
            self::OPERATIONS_ACTIVITY => $this->operationsActivity($period),
            self::LOGBOOK_ACTIVITY => $this->logbookActivity($period),
            default => throw new InvalidArgumentException("Unknown report: {$code}"),
        };
    }

    private function operationsActivity(ReportPeriod $period): ExportDataset
    {
        $operations = Operation::query()
            ->with('category')
            ->whereBetween('started_at', [$period->start, $period->end])
            ->orderBy('started_at')
            ->get();

        return new ExportDataset(
            $this->title(self::OPERATIONS_ACTIVITY, $period),
            OperationRows::headings(),
            $operations->map(fn (Operation $operation) => OperationRows::row($operation))->all(),
        );
    }

    private function logbookActivity(ReportPeriod $period): ExportDataset
    {
        $rows = LogbookEntry::query()
            ->with('channel')
            ->whereBetween('occurred_at', [$period->start, $period->end])
            ->get()
            ->groupBy('channel_id')
            ->map(fn ($entries) => [
                $entries->first()->channel?->label,
                $entries->count(),
                $entries->where('is_order_or_decision', true)->count(),
            ])
            ->sortByDesc(1)
            ->values()
            ->all();

        return new ExportDataset(
            $this->title(self::LOGBOOK_ACTIVITY, $period),
            collect(['channel', 'entries', 'orders_or_decisions'])->map(fn (string $column) => __("reports::reports.columns.{$column}"))->all(),
            $rows,
        );
    }

    private function title(string $code, ReportPeriod $period): string
    {
        return $this->label($code).' - '.$period->label;
    }
}
