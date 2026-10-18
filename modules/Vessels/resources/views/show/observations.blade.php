<x-ui.card :title="__('vessels::vessels.tabs.observations')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.level') }}</th>
            <th>{{ __('vessels::vessels.columns.description') }}</th>
            <th>{{ __('vessels::vessels.columns.author') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
        </x-slot:head>
        @forelse ($vessel->observations as $observation)
            <tr>
                <td class="text-nowrap">{{ $observation->observed_at->isoFormat('L LT') }}</td>
                <td><x-ui.status-badge :status="$observation->level" /></td>
                <td class="text-wrap">{{ $observation->description }}</td>
                <td>{{ $observation->author->name }}</td>
                <td><x-ui.status-badge :status="$observation->status" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5" />
        @endforelse
    </x-ui.table>
</x-ui.card>
