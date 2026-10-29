<x-layouts.app :title="__('communications::notes.title')">
    <x-ui.page-header :title="__('communications::notes.title')" :subtitle="__('communications::notes.subtitle')" />

    <div class="page-body">
        @include('communications::partials.tabs', ['active' => 'notes'])

        <div class="row">
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('communications.notes.index', ['box' => 'inbox', 'unread' => 1]) }}">
                    <x-ui.stat-card :label="__('communications::notes.unread')" :value="Number::format($unreadCount)" icon="icon-edit" :color="$unreadCount > 0 ? 'warning' : 'success'" />
                </a>
            </div>
        </div>

        <x-ui.card :title="__('communications::notes.list_title')" :subtitle="trans_choice('ui.results_count', $notes->total())" flush>
            @can(App\Support\Authorization\Permission::CommunicationsCreateNote->value)
                <x-slot:actions>
                    <a href="{{ route('communications.notes.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('communications::notes.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table-toolbar :action="route('communications.notes.index')" :paginator="$notes">
                <x-ui.toolbar-select name="box" :label="__('communications::notes.box')" :options="['inbox' => __('communications::notes.inbox'), 'sent' => __('communications::notes.sent')]" :value="$box" />
                <x-ui.toolbar-select name="unread" :label="__('communications::notes.unread')" :options="['1' => __('ui.yes')]" :value="$filters['unread'] ?? null" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$notes">
                <x-slot:head>
                    <th>{{ __('communications::fields.type') }}</th>
                    <th>{{ __('communications::fields.urgency') }}</th>
                    <th>{{ __('communications::fields.vessel_id') }}</th>
                    <th>{{ __('communications::fields.free_text') }}</th>
                    <th>{{ __('communications::notes.author') }}</th>
                    <th>{{ __('communications::fields.recipient') }}</th>
                    <th>{{ __('communications::fields.due_date') }}</th>
                    <th>{{ __('communications::notes.state') }}</th>
                    <th></th>
                </x-slot:head>

                <x-slot:filters>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </x-slot:filters>

                @forelse ($notes as $note)
                    <tr>
                        <td class="f-w-600">{{ __($note->type->label()) }}</td>
                        <td><x-ui.status-badge :status="$note->urgency" /></td>
                        <td>
                            @if ($note->vessel)
                                <x-ui.vessel-link :vessel="$note->vessel" />
                            @endif
                        </td>
                        <td class="text-pre-line">{{ $note->free_text }}</td>
                        <td>
                            {{ $note->author->name }}
                            @if ($note->aiAgentAction)
                                <span class="label label-info">{{ __('communications::notes.by_ai_agent') }}</span>
                            @endif
                        </td>
                        <td>{{ $note->recipient?->name ?? $note->recipientTeam?->name }}</td>
                        <td>
                            {{ $note->due_date?->isoFormat('L') }}
                            @if ($note->isOverdue())
                                <span class="label label-danger">{{ __('communications::notes.overdue') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="label label-{{ $note->is_read ? 'success' : 'warning' }}">{{ $note->is_read ? __('communications::notes.read') : __('communications::notes.not_read') }}</span>
                            @if ($note->relayed_by)
                                <span class="label label-default">{{ __($note->relayed_by->label()) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if (! $note->is_read && $note->isAddressedTo(auth()->user()))
                                <form method="POST" action="{{ route('communications.notes.read', $note) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-default btn-sm">{{ __('communications::notes.mark_read') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="9" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
