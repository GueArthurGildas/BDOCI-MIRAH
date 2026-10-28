<x-ui.card :title="__('vessels::vessels.tabs.communications')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.recipient') }}</th>
            <th>{{ __('vessels::vessels.columns.subject') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
            <th>{{ __('vessels::vessels.columns.reply') }}</th>
        </x-slot:head>
        @forelse ($vessel->sentMessages as $message)
            <tr>
                <td>{{ $message->sent_at?->isoFormat('L LT') ?? '—' }}</td>
                <td>{{ $message->recipient }}</td>
                <td>{{ $message->subject }}</td>
                <td><x-ui.status-badge :status="$message->status" /></td>
                <td>{{ $message->reply_received ? __('ui.yes') : __('ui.no') }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5" />
        @endforelse
    </x-ui.table>
</x-ui.card>

<x-ui.card :title="__('vessels::vessels.notes_title')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.type') }}</th>
            <th>{{ __('vessels::vessels.columns.description') }}</th>
        </x-slot:head>
        @forelse ($vessel->internalNotes as $note)
            <tr>
                <td>{{ $note->created_at?->isoFormat('L LT') ?? '—' }}</td>
                <td>{{ __($note->type->label()) }}</td>
                <td>{{ $note->free_text }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="3" />
        @endforelse
    </x-ui.table>
</x-ui.card>
