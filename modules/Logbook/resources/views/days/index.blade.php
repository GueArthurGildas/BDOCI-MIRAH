@php
    $keep = request()->except('page', 'day', 'folder', 'item');
@endphp
<x-layouts.app :title="__('logbook::logbook.days.title')">
    <x-ui.page-header :title="__('logbook::logbook.days.title')" :subtitle="__('logbook::logbook.days.subtitle')">
        <x-slot:actions>
            <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('logbook::logbook.views.label') }}">
                <a href="{{ route('logbook.days.index', [...$keep, 'view' => 'list']) }}" @class(['btn', 'btn-primary' => $view === 'list', 'btn-default' => $view !== 'list'])><i class="feather icon-list"></i> {{ __('logbook::logbook.views.list') }}</a>
                <a href="{{ route('logbook.days.index', [...$keep, 'view' => 'folders']) }}" @class(['btn', 'btn-primary' => $view === 'folders', 'btn-default' => $view !== 'folders'])><i class="feather icon-folder"></i> {{ __('logbook::logbook.views.folders') }}</a>
            </div>
            <a href="{{ route('logbook.days.today') }}" class="btn btn-sm btn-outline-primary"><i class="feather icon-calendar"></i> {{ __('logbook::logbook.days.open_today') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="page-body">
        <x-ui.filters :action="route('logbook.days.index')">
            <input type="hidden" name="view" value="{{ $view }}">
            <x-ui.filter-input name="search" type="search" :label="__('logbook::logbook.filters.search')" :value="$filters['search'] ?? null" :placeholder="__('logbook::logbook.filters.search_placeholder')" data-auto-submit class="col-md-3" />
            <x-ui.filter-select name="status" :label="__('logbook::logbook.days.fields.status')" :options="$statuses" :value="$filters['status'] ?? null" data-auto-submit class="col-md-2" />
            <x-ui.filter-select name="channel" :label="__('logbook::logbook.entries.fields.channel')" :options="$channels" :value="$filters['channel'] ?? null" data-auto-submit class="col-md-2" />
            <x-ui.filter-input name="from" type="date" :label="__('logbook::logbook.filters.from')" :value="$filters['from'] ?? null" data-auto-submit class="col-md-2" />
            <x-ui.filter-input name="to" type="date" :label="__('logbook::logbook.filters.to')" :value="$filters['to'] ?? null" data-auto-submit class="col-md-2" />
        </x-ui.filters>

        @if ($view === 'list')
            <x-ui.card :title="__('logbook::logbook.days.list_title')" :subtitle="trans_choice('ui.results_count', $days->total())" flush>
                <x-ui.table :paginator="$days">
                    <x-slot:head>
                        <th>{{ __('logbook::logbook.days.fields.day') }}</th>
                        <th>{{ __('logbook::logbook.days.fields.shift') }}</th>
                        <th>{{ __('logbook::logbook.days.fields.entries') }}</th>
                        <th>{{ __('logbook::logbook.days.fields.status') }}</th>
                        <th></th>
                    </x-slot:head>

                    @forelse ($days as $day)
                        <tr>
                            <td class="text-nowrap">{{ $day->day->isoFormat('dddd L') }}</td>
                            <td class="text-nowrap">{{ $day->shift_start_at->format('H:i') }} - {{ $day->shift_end_at->format('H:i') }}</td>
                            <td>{{ $day->entries_count }}</td>
                            <td><x-ui.status-badge :status="$day->status" /></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('logbook.days.word', $day) }}" class="btn btn-default btn-mini" title="{{ __('logbook::logbook.days.word') }}"><i class="feather icon-file-text"></i></a>
                                <a href="{{ route('logbook.days.show', $day) }}" class="btn btn-outline-primary btn-mini">{{ __('ui.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <x-ui.empty-row colspan="5" />
                    @endforelse
                </x-ui.table>
            </x-ui.card>
        @else
            @include('logbook::days.folders')
        @endif
    </div>
</x-layouts.app>
