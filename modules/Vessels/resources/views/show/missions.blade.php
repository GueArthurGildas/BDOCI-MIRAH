<x-ui.card :title="__('vessels::vessels.tabs.missions')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.reference') }}</th>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.type') }}</th>
            <th>{{ __('vessels::vessels.columns.objective') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
        </x-slot:head>
        @forelse ($vessel->missions as $mission)
            <tr>
                <td>{{ $mission->reference }}</td>
                <td>{{ $mission->starts_at->isoFormat('L') }}</td>
                <td>{{ __($mission->type->label()) }}</td>
                <td class="text-wrap">{{ $mission->objective }}</td>
                <td><x-ui.status-badge :status="$mission->status" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5" />
        @endforelse
    </x-ui.table>
</x-ui.card>
