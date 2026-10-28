<?php

namespace Modules\Reports\Support;

use App\Models\LogbookEntry;
use App\Models\Operation;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Builder;

class ExportDatasets
{
    /** @param array<string, mixed> $filters */
    public function build(ExportType $type, array $filters): ExportDataset
    {
        return match ($type) {
            ExportType::Operations => $this->operations($filters),
            ExportType::Vessels => $this->vessels($filters),
            ExportType::Logbook => $this->logbook($filters),
        };
    }

    /** @param array<string, mixed> $filters */
    private function operations(array $filters): ExportDataset
    {
        $operations = Operation::query()
            ->with('category')
            ->when($filters['year'] ?? null, fn (Builder $query, $year) => $query->where('year', $year))
            ->when($filters['status'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->orderBy('started_at')
            ->get();

        return new ExportDataset(
            __(ExportType::Operations->label()),
            OperationRows::headings(),
            $operations->map(fn (Operation $operation) => OperationRows::row($operation))->all(),
        );
    }

    /** @param array<string, mixed> $filters */
    private function vessels(array $filters): ExportDataset
    {
        $vessels = Vessel::query()
            ->with(['flagCountry', 'currentOwner'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('registration_number', "%{$search}%")))
            ->when($filters['record_status'] ?? null, fn (Builder $query, $status) => $query->where('record_status', $status))
            ->orderBy('name')
            ->get();

        return new ExportDataset(
            __(ExportType::Vessels->label()),
            collect(['name', 'registration_number', 'imo_number', 'call_sign', 'flag', 'owner', 'gross_tonnage', 'record_status'])
                ->map(fn (string $column) => __("reports::reports.columns.{$column}"))->all(),
            $vessels->map(fn (Vessel $vessel) => [
                $vessel->name,
                $vessel->registration_number,
                $vessel->imo_number,
                $vessel->call_sign,
                $vessel->flag,
                $vessel->owner,
                $vessel->gross_tonnage,
                $vessel->record_status ? __($vessel->record_status->label()) : null,
            ])->all(),
        );
    }

    /** @param array<string, mixed> $filters */
    private function logbook(array $filters): ExportDataset
    {
        $entries = LogbookEntry::query()
            ->with(['channel', 'operation', 'vessels'])
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('occurred_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('occurred_at', '<', date('Y-m-d', strtotime("{$to} +1 day"))))
            ->orderBy('occurred_at')
            ->get();

        return new ExportDataset(
            __(ExportType::Logbook->label()),
            collect(['occurred_at', 'channel', 'direction', 'content', 'vessels', 'operation', 'handling_status'])
                ->map(fn (string $column) => __("reports::reports.columns.{$column}"))->all(),
            $entries->map(fn (LogbookEntry $entry) => [
                $entry->occurred_at->format('Y-m-d H:i'),
                $entry->channel?->label,
                __($entry->direction->label()),
                $entry->content,
                $entry->vessels->pluck('name')->implode(', '),
                $entry->operation?->code,
                __($entry->handling_status->label()),
            ])->all(),
        );
    }
}
