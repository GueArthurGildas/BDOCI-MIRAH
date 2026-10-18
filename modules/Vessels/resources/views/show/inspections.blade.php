<x-ui.card :title="__('vessels::vessels.tabs.inspections')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.reference') }}</th>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.type') }}</th>
            <th>{{ __('vessels::vessels.columns.port') }}</th>
            <th>{{ __('vessels::vessels.columns.result') }}</th>
        </x-slot:head>
        @forelse ($vessel->inspections as $inspection)
            <tr>
                <td>{{ $inspection->external_reference }}</td>
                <td>{{ $inspection->inspected_at->isoFormat('L') }}</td>
                <td>{{ __($inspection->type->label()) }}</td>
                <td>{{ $inspection->port?->name ?? '—' }}</td>
                <td><x-ui.status-badge :status="$inspection->result" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5" />
        @endforelse
    </x-ui.table>
</x-ui.card>
