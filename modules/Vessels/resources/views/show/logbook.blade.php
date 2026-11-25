@can(App\Support\Authorization\Permission::OperationsView->value)
    <x-ui.card :title="__('vessels::vessels.logbook.operations')" :subtitle="__('vessels::vessels.logbook.operations_hint')" flush>
        <x-ui.table>
            <x-slot:head>
                <th>{{ __('vessels::vessels.columns.reference') }}</th>
                <th>{{ __('vessels::vessels.columns.type') }}</th>
                <th>{{ __('vessels::vessels.logbook.opened_at') }}</th>
                <th>{{ __('vessels::vessels.columns.closed_at') }}</th>
                <th>{{ __('vessels::vessels.columns.status') }}</th>
            </x-slot:head>
            @forelse ($vessel->operations as $operation)
                <tr id="item-logbook-operation-{{ $operation->id }}">
                    <td class="text-wrap"><a href="{{ route('operations.show', $operation) }}">{{ $operation->code }}</a></td>
                    <td>{{ $operation->category?->code?->value }}</td>
                    <td class="text-nowrap">{{ $operation->started_at?->isoFormat('L LT') }}</td>
                    <td class="text-nowrap">{{ $operation->ended_at?->isoFormat('L LT') ?? '—' }}</td>
                    <td><x-ui.status-badge :status="$operation->status" /></td>
                </tr>
            @empty
                <x-ui.empty-row colspan="5" />
            @endforelse
        </x-ui.table>
    </x-ui.card>
@endcan

<x-ui.card :title="__('vessels::vessels.logbook.entries')" :subtitle="__('vessels::vessels.logbook.entries_hint', ['count' => $vessel->logbookEntries->count()])" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.logbook.when') }}</th>
            <th>{{ __('vessels::vessels.logbook.how') }}</th>
            <th>{{ __('vessels::vessels.logbook.what') }}</th>
            <th>{{ __('vessels::vessels.logbook.handling') }}</th>
        </x-slot:head>
        @forelse ($vessel->logbookEntries as $entry)
            <tr id="item-logbook-{{ $entry->id }}">
                <td class="text-nowrap">
                    <a href="{{ route('logbook.days.show', $entry->logbook_day_id) }}#entry-{{ $entry->id }}">{{ $entry->occurred_at->isoFormat('L') }}</a>
                    <div class="text-muted f-12">{{ $entry->occurred_at->format('H:i') }} UTC</div>
                </td>
                <td>
                    {{ $entry->channel?->label }}
                    <div class="text-muted f-12">{{ __($entry->direction->label()) }}</div>
                </td>
                <td class="text-wrap">
                    @if ($entry->operation_milestone)
                        <x-ui.status-badge :status="$entry->operation_milestone" />
                    @endif
                    <span @class(['text-pre-line', 'gs-cancelled' => $entry->isCancelled(), 'text-danger' => $entry->operation_milestone === App\Enums\OperationMilestone::Opened, 'text-success' => $entry->operation_milestone === App\Enums\OperationMilestone::Closed])>{{ $entry->content }}</span>
                    @if ($entry->isCancelled())
                        <div class="text-danger f-12">{{ __('ui.deletion.cancelled', ['name' => $entry->canceller?->fullName() ?? '—', 'date' => $entry->cancelled_at->isoFormat('L LT'), 'reason' => $entry->cancellation_reason]) }}</div>
                    @endif
                    @if ($entry->operation)
                        <div><a href="{{ route('operations.show', $entry->operation) }}">{{ $entry->operation->code }}</a> <x-ui.status-badge :status="$entry->operation->status" /></div>
                    @endif
                    @if ($entry->observation)
                        <div class="text-muted f-12">{{ __('vessels::vessels.logbook.observation', ['id' => $entry->observation->id]) }}</div>
                    @elseif ($entry->surveillanceObservation)
                        <div class="text-muted f-12">{{ __('vessels::vessels.logbook.surveillance_observation', ['id' => $entry->surveillanceObservation->id]) }}</div>
                    @endif
                    @if ($entry->witnessReport)
                        <div class="text-muted f-12">{{ __('vessels::vessels.logbook.witness_report', ['id' => $entry->witnessReport->id]) }}</div>
                    @endif
                </td>
                <td><x-ui.status-badge :status="$entry->handling_status" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
