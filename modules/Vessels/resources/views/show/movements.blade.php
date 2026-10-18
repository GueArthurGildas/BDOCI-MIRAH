<x-ui.card :title="__('vessels::vessels.tabs.movements')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.direction') }}</th>
            <th>{{ __('vessels::vessels.columns.port') }}</th>
            <th>{{ __('vessels::vessels.columns.reason') }}</th>
        </x-slot:head>
        @forelse ($vessel->movements as $movement)
            <tr>
                <td>{{ $movement->occurred_at->isoFormat('L LT') }}</td>
                <td><x-ui.status-badge :status="$movement->direction" /></td>
                <td>{{ $movement->port->name }}</td>
                <td>{{ $movement->reason }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
