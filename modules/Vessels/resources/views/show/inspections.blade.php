<x-ui.card :title="__('vessels::vessels.tabs.inspections')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.reference') }}</th>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.type') }}</th>
            <th>{{ __('vessels::vessels.columns.port') }}</th>
            <th>{{ __('vessels::vessels.columns.result') }}</th>
        </x-slot:head>
        @forelse ($vessel->externalInspections as $inspection)
            <tr>
                <td><a href="{{ route('inspections.show', $inspection) }}">{{ $inspection->reference ?? $inspection->external_id }}</a></td>
                <td>{{ $inspection->started_at?->isoFormat('L') ?? '—' }}</td>
                <td>{{ $inspection->control_type ?? '—' }}</td>
                <td>{{ $inspection->port?->name ?? '—' }}</td>
                <td>{{ $inspection->resultLabel() }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5" />
        @endforelse
    </x-ui.table>
</x-ui.card>
