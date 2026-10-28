<x-layouts.app :title="__('communications::communications.title')">
    <x-ui.page-header :title="__('communications::communications.title')" :subtitle="__('communications::communications.subtitle')" />

    <div class="page-body">
        @include('communications::partials.tabs', ['active' => 'messages'])

        <div class="row">
            @foreach (App\Enums\MessageStatus::cases() as $status)
                <div class="col-xl col-md-4">
                    <a href="{{ route('communications.messages.index', ['status' => $status->value]) }}">
                        <x-ui.stat-card :label="__($status->label())" :value="Number::format($countsByStatus[$status->value])" icon="icon-mail" :color="$status->color() === 'default' ? 'info' : $status->color()" />
                    </a>
                </div>
            @endforeach
        </div>

        <x-ui.filters :action="route('communications.messages.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('communications::messages.search_placeholder')" class="col-md-5" />
            <x-ui.filter-select name="status" :label="__('communications::fields.status')" :options="App\Enums\MessageStatus::options()" :value="$filters['status'] ?? null" />
            <x-ui.filter-select name="channel" :label="__('communications::fields.channel')" :options="App\Enums\CommunicationChannelCode::options()" :value="$filters['channel'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('communications::messages.list_title')" :subtitle="trans_choice('ui.results_count', $messages->total())" flush>
            @can(App\Support\Authorization\Permission::CommunicationsSend->value)
                <x-slot:actions>
                    <a href="{{ route('communications.messages.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-send"></i> {{ __('communications::messages.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table :paginator="$messages">
                <x-slot:head>
                    <th>{{ __('communications::fields.sent_at') }}</th>
                    <th>{{ __('communications::fields.vessel_id') }}</th>
                    <th>{{ __('communications::fields.channel') }}</th>
                    <th>{{ __('communications::fields.recipient') }}</th>
                    <th>{{ __('communications::fields.triggered_by') }}</th>
                    <th>{{ __('communications::fields.status') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($messages as $message)
                    <tr>
                        <td>{{ ($message->sent_at ?? $message->created_at)->isoFormat('L LT') }}</td>
                        <td>
                            @if ($message->vessel)
                                <x-ui.vessel-link :vessel="$message->vessel" />
                            @endif
                        </td>
                        <td>{{ __($message->channel->code->label()) }}</td>
                        <td>{{ $message->recipient }}</td>
                        <td>{{ __($message->triggered_by->label()) }}</td>
                        <td><x-ui.status-badge :status="$message->status" /></td>
                        <td class="text-end"><a href="{{ route('communications.messages.show', $message) }}" class="btn btn-default btn-sm">{{ __('ui.view') }}</a></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
