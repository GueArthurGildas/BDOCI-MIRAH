<?php

namespace Modules\Reports\Support;

use App\Models\EezDeclaration;
use App\Models\ErsDeclaration;
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
            ExportType::EezDeclarations => $this->eezDeclarations($filters),
            ExportType::ErsDeclarations => $this->ersDeclarations($filters),
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

    /** @param array<string, mixed> $filters */
    private function eezDeclarations(array $filters): ExportDataset
    {
        $declarations = EezDeclaration::query()
            ->with(['vessel', 'receptionChannel', 'enteredBy'])
            ->withSum('catches', 'weight_kg')
            ->when($filters['vessel'] ?? null, fn (Builder $query, $vessel) => $query->where('vessel_id', $vessel))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('received_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('received_at', '<=', $to))
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();

        return new ExportDataset(
            __(ExportType::EezDeclarations->label()),
            collect(['received_at', 'type', 'vessel', 'reason', 'crossed_at', 'position', 'route', 'speed', 'destination', 'declared_name', 'declared_imo', 'declared_mmsi', 'declared_ircs', 'declared_flag', 'declared_owner', 'channel', 'entry_origin', 'entered_by', 'status', 'catches_kg'])
                ->map(fn (string $column) => __("reports::reports.columns.{$column}"))->all(),
            $declarations->map(fn (EezDeclaration $declaration) => [
                $declaration->received_at->format('Y-m-d H:i'),
                __($declaration->type->label()),
                $declaration->vessel?->name ?? $declaration->declared_name,
                $declaration->reason ? __($declaration->reason->label()) : null,
                $declaration->crossed_at?->format('Y-m-d H:i'),
                $declaration->latitude !== null && $declaration->longitude !== null ? "{$declaration->latitude}, {$declaration->longitude}" : null,
                $declaration->route,
                $declaration->speed,
                $declaration->destination,
                $declaration->declared_name,
                $declaration->declared_imo,
                $declaration->declared_mmsi,
                $declaration->declared_ircs,
                $declaration->declared_flag,
                $declaration->declared_owner,
                $declaration->receptionChannel?->label,
                __($declaration->entry_origin->label()),
                $declaration->enteredBy?->name,
                __($declaration->status->label()),
                $declaration->catches_sum_weight_kg,
            ])->all(),
        );
    }

    /** @param array<string, mixed> $filters */
    private function ersDeclarations(array $filters): ExportDataset
    {
        $declarations = ErsDeclaration::query()
            ->with(['vessel', 'lines.species', 'lines.presentation', 'lines.gear'])
            ->when($filters['vessel'] ?? null, fn (Builder $query, $vessel) => $query->where('vessel_id', $vessel))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('declared_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('declared_at', '<=', $to))
            ->orderBy('declared_at')
            ->orderBy('id')
            ->get();

        return new ExportDataset(
            __(ExportType::ErsDeclarations->label()),
            collect(['declared_at', 'vessel', 'source', 'species', 'presentation', 'conversion_factor', 'live_weight_kg', 'fao_zone', 'gear'])
                ->map(fn (string $column) => __("reports::reports.columns.{$column}"))->all(),
            $declarations->flatMap(fn (ErsDeclaration $declaration) => ($declaration->lines->isEmpty() ? collect([null]) : $declaration->lines)->map(fn ($line) => [
                $declaration->declared_at->format('Y-m-d H:i'),
                $declaration->vessel?->name,
                $declaration->source ? __($declaration->source->label()) : null,
                $line?->species?->name,
                $line?->presentation?->label,
                $line?->conversion_factor,
                $line?->live_weight_kg,
                $line?->fao_zone,
                $line?->gear?->label,
            ]))->values()->all(),
        );
    }
}
