<x-ui.card :title="__('vessels::vessels.tabs.comments')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.author') }}</th>
            <th>{{ __('vessels::vessels.columns.description') }}</th>
        </x-slot:head>
        @forelse ($vessel->comments as $comment)
            <tr>
                <td>{{ $comment->created_at?->isoFormat('L LT') ?? '—' }}</td>
                <td>{{ $comment->author?->name ?? '—' }}</td>
                <td>{{ $comment->body }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="3" />
        @endforelse
    </x-ui.table>
</x-ui.card>
