<x-ui.card :title="__('vessels::vessels.tabs.eez')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.type') }}</th>
            <th>{{ __('vessels::vessels.columns.reason') }}</th>
            <th>{{ __('vessels::vessels.columns.destination') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
        </x-slot:head>
        @forelse ($vessel->eezDeclarations as $declaration)
            <tr>
                <td>{{ $declaration->received_at->isoFormat('L LT') }}</td>
                <td>{{ __($declaration->type->label()) }}</td>
                <td>{{ $declaration->reason ? __($declaration->reason->label()) : '—' }}</td>
                <td>{{ $declaration->destination ?: '—' }}</td>
                <td><x-ui.status-badge :status="$declaration->status" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5" />
        @endforelse
    </x-ui.table>
</x-ui.card>
