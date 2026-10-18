<x-ui.card :title="__('vessels::vessels.tabs.infractions')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.reference') }}</th>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.infraction_type') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
        </x-slot:head>
        @forelse ($vessel->infractions as $infraction)
            <tr>
                <td>{{ $infraction->reference }}</td>
                <td>{{ $infraction->detected_at->isoFormat('L') }}</td>
                <td>{{ $infraction->type->name }}</td>
                <td><x-ui.status-badge :status="$infraction->status" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
