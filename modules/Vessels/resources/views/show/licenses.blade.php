<x-ui.card :title="__('vessels::vessels.tabs.licenses')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.number') }}</th>
            <th>{{ __('vessels::vessels.columns.type') }}</th>
            <th>{{ __('vessels::vessels.columns.valid_from') }}</th>
            <th>{{ __('vessels::vessels.columns.valid_until') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
        </x-slot:head>
        @forelse ($vessel->licenses as $license)
            <tr>
                <td><a href="{{ route('licenses.show', $license) }}">{{ $license->number }}</a></td>
                <td>{{ $license->segment ? __($license->segment->label()) : '—' }}</td>
                <td>{{ $license->issued_on?->isoFormat('L') ?? '—' }}</td>
                <td>{{ $license->expires_on->isoFormat('L') }}</td>
                <td><x-ui.status-badge :status="$license->standing" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5" />
        @endforelse
    </x-ui.table>
</x-ui.card>
