<x-ui.card :title="__('dashboard::dashboard.messages.title')" :subtitle="trans_choice('dashboard::dashboard.messages.unread', $unreadMessages, ['count' => $unreadMessages])" flush data-guide="messages">
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('dashboard::dashboard.columns.date') }}</th>
            <th>{{ __('dashboard::dashboard.messages.from') }}</th>
            <th>{{ __('dashboard::dashboard.columns.level') }}</th>
            <th>{{ __('dashboard::dashboard.messages.content') }}</th>
        </x-slot:head>
        @forelse ($messages as $message)
            <tr>
                <td class="text-nowrap">{{ $message->created_at->isoFormat('L LT') }}</td>
                <td>{{ $message->author?->name }}</td>
                <td><x-ui.status-badge :status="$message->urgency" /></td>
                <td class="text-wrap">{{ Str::limit($message->free_text, 120) }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4">{{ __('dashboard::dashboard.messages.none') }}</x-ui.empty-row>
        @endforelse
    </x-ui.table>
    @can(App\Support\Authorization\Permission::CommunicationsView->value)
        <div class="card-footer text-end">
            <a href="{{ route('communications.notes.index') }}">{{ __('dashboard::dashboard.messages.all') }}</a>
        </div>
    @endcan
</x-ui.card>
