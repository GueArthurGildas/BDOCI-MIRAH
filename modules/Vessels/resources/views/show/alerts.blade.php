<x-ui.card :title="__('vessels::vessels.tabs.alerts')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.type') }}</th>
            <th>{{ __('vessels::vessels.columns.zone') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
        </x-slot:head>
        @forelse ($vessel->alerts as $alert)
            <tr>
                <td>{{ $alert->triggered_at->isoFormat('L LT') }}</td>
                <td>{{ __($alert->type->label()) }}</td>
                <td>{{ $alert->zone?->name ?? '—' }}</td>
                <td><x-ui.status-badge :status="$alert->status" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
