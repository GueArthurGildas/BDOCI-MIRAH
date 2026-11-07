<x-ui.card :title="$title" :subtitle="trans_choice('profile::profile.messages.unread', $unread, ['count' => $unread])" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('profile::profile.columns.date') }}</th>
            <th>{{ __('profile::profile.columns.from') }}</th>
            <th>{{ __('profile::profile.columns.urgency') }}</th>
            <th>{{ __('profile::profile.columns.vessel') }}</th>
            <th>{{ __('profile::profile.columns.message') }}</th>
        </x-slot:head>
        @forelse ($notes as $note)
            <tr @class(['fw-semibold' => ! $note->is_read])>
                <td class="text-nowrap">{{ $note->created_at->isoFormat('L LT') }}</td>
                <td>{{ $note->author?->fullName() }}</td>
                <td><x-ui.status-badge :status="$note->urgency" /></td>
                <td>@if ($note->vessel)<x-ui.vessel-link :vessel="$note->vessel" :thumb="false" />@endif</td>
                <td class="text-wrap text-pre-line">{{ $note->free_text }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="5">{{ __('profile::profile.empty') }}</x-ui.empty-row>
        @endforelse
    </x-ui.table>
    @can(App\Support\Authorization\Permission::CommunicationsView->value)
        <div class="card-footer text-end"><a href="{{ route('communications.notes.index') }}">{{ __('profile::profile.messages.all') }}</a></div>
    @endcan
</x-ui.card>
