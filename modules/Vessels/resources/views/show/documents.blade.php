<x-ui.card :title="__('vessels::vessels.tabs.documents')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.name') }}</th>
            <th>{{ __('vessels::vessels.columns.origin') }}</th>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
        </x-slot:head>
        @forelse ($vessel->licenseDocuments as $link)
            <tr>
                <td>{{ $link->document?->name }}</td>
                <td>{{ __('vessels::vessels.documents.license') }}</td>
                <td>{{ $link->document?->created_at?->isoFormat('L') ?? '—' }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="3" />
        @endforelse
    </x-ui.table>
</x-ui.card>
