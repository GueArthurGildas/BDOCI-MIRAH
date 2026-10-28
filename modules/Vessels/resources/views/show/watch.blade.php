<x-ui.card :title="__('vessels::vessels.tabs.watch')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.origin') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
            <th>{{ __('vessels::vessels.columns.closed_at') }}</th>
        </x-slot:head>
        @forelse ($vessel->watchEntries as $entry)
            <tr>
                <td>{{ $entry->started_at->isoFormat('L LT') }}</td>
                <td>{{ __($entry->origin->label()) }}</td>
                <td><x-ui.status-badge :status="$entry->status" /></td>
                <td>{{ $entry->closed_at?->isoFormat('L LT') ?? '—' }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
