@php
    $link = fn (int $dayId, string $folder = 'summary', ?int $item = null) => route('logbook.days.index', [...$keep, 'view' => 'folders', 'day' => $dayId, 'folder' => $folder, 'item' => $item]);
@endphp

@if ($days->isEmpty())
    <x-ui.card :title="__('logbook::logbook.days.list_title')">
        <p class="text-muted mb-0">{{ __('ui.no_results') }}</p>
    </x-ui.card>
@else
    <div class="row">
        <div class="col-xl-3">
            <x-ui.card class="gs-tree-card" :title="__('logbook::logbook.folders.title')" :subtitle="trans_choice('ui.results_count', $days->count())">
                <x-slot:actions>
                    <ul class="list-unstyled card-option">
                        <li><a href="#" data-gs-tree="open"><i class="feather icon-plus-square"></i></a></li>
                        <li><a href="#" data-gs-tree="close"><i class="feather icon-minus-square"></i></a></li>
                    </ul>
                </x-slot:actions>

                <ul class="gs-tree" data-gs-tree-root>
                    @foreach ($tree as $year => $months)
                        <li>
                            <details @if ($selected && $selected->day->year === $year) open @endif>
                                <summary><i class="feather icon-folder"></i> <span>{{ $year }}</span> <span class="gs-tree-count">{{ $months->flatten(1)->count() }}</span></summary>
                                <ul>
                                    @foreach ($months as $monthKey => $monthDays)
                                        <li>
                                            <details @if ($selected && $selected->day->format('Y-m') === $monthKey) open @endif>
                                                <summary><i class="feather icon-folder"></i> <span>{{ $monthDays->first()->day->isoFormat('MMMM') }}</span> <span class="gs-tree-count">{{ $monthDays->count() }}</span></summary>
                                                <ul>
                                                    @foreach ($monthDays as $day)
                                                        @php($isSelected = $selected && $selected->id === $day->id)
                                                        <li>
                                                            @unless ($isSelected)
                                                                <a href="{{ $link($day->id) }}" class="gs-tree-leaf">
                                                                    <i class="feather icon-book"></i> <span>{{ $day->day->isoFormat('ddd D') }}</span>
                                                                    <span class="gs-tree-count">{{ $day->entries_count }}</span>
                                                                </a>
                                                            @else
                                                                <details open>
                                                                    <summary class="is-active">
                                                                        <a href="{{ $link($day->id) }}"><i class="feather icon-book"></i> {{ $day->day->isoFormat('ddd D') }}</a>
                                                                        <span class="gs-tree-count">{{ $day->entries_count }}</span>
                                                                    </summary>
                                                                    <ul>
                                                                        <li>
                                                                            <details @if ($folder === 'entries') open @endif>
                                                                                <summary @class(['is-active' => $folder === 'entries' && ! $item])><a href="{{ $link($day->id, 'entries') }}"><i class="feather icon-list"></i> {{ __('logbook::logbook.folders.entries') }}</a><span class="gs-tree-count">{{ $content['entries']->count() }}</span></summary>
                                                                                <ul>@foreach ($content['channels'] as $channel)<li><a href="{{ $link($day->id, 'entries', $channel['id']) }}"><i class="feather icon-file"></i> {{ $channel['label'] }} <span class="gs-tree-count">{{ $channel['count'] }}</span></a></li>@endforeach</ul>
                                                                            </details>
                                                                        </li>
                                                                        <li>
                                                                            <details @if ($folder === 'vessels') open @endif>
                                                                                <summary @class(['is-active' => $folder === 'vessels' && ! $item])><a href="{{ $link($day->id, 'vessels') }}"><i class="feather icon-anchor"></i> {{ __('logbook::logbook.folders.vessels') }}</a><span class="gs-tree-count">{{ $content['vessels']->count() }}</span></summary>
                                                                                <ul>@foreach ($content['vessels'] as $vessel)<li><a href="{{ $link($day->id, 'vessels', $vessel['id']) }}"><i class="feather icon-file"></i> {{ $vessel['label'] }} <span class="gs-tree-count">{{ $vessel['count'] }}</span></a></li>@endforeach</ul>
                                                                            </details>
                                                                        </li>
                                                                        <li>
                                                                            <details @if ($folder === 'actors') open @endif>
                                                                                <summary @class(['is-active' => $folder === 'actors' && ! $item])><a href="{{ $link($day->id, 'actors') }}"><i class="feather icon-users"></i> {{ __('logbook::logbook.folders.actors') }}</a><span class="gs-tree-count">{{ $content['actors']->count() }}</span></summary>
                                                                                <ul>@foreach ($content['actors'] as $actor)<li><a href="{{ $link($day->id, 'actors', $actor['id']) }}"><i class="feather icon-file"></i> {{ $actor['label'] }} <span class="gs-tree-count">{{ $actor['count'] }}</span></a></li>@endforeach</ul>
                                                                            </details>
                                                                        </li>
                                                                        <li>
                                                                            <details @if ($folder === 'operations') open @endif>
                                                                                <summary @class(['is-active' => $folder === 'operations' && ! $item])><a href="{{ $link($day->id, 'operations') }}"><i class="feather icon-layers"></i> {{ __('logbook::logbook.folders.operations') }}</a><span class="gs-tree-count">{{ $content['operations']->count() }}</span></summary>
                                                                                <ul>@foreach ($content['operations'] as $operation)<li><a href="{{ $link($day->id, 'operations', $operation['id']) }}"><i class="feather icon-file"></i> {{ $operation['label'] }} <span class="gs-tree-count">{{ $operation['count'] }}</span></a></li>@endforeach</ul>
                                                                            </details>
                                                                        </li>
                                                                        <li>
                                                                            <a href="{{ $link($day->id, 'decisions') }}" @class(['gs-tree-leaf', 'is-active' => $folder === 'decisions'])><i class="feather icon-alert-octagon"></i> <span>{{ __('logbook::logbook.folders.decisions') }}</span><span class="gs-tree-count">{{ $content['decisions'] }}</span></a>
                                                                        </li>
                                                                    </ul>
                                                                </details>
                                                            @endunless
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>

        <div class="col-xl-9">
            <x-ui.card :title="$selected->day->isoFormat('dddd LL')" :subtitle="__('logbook::logbook.days.shift_range', ['from' => $selected->shift_start_at->format('H:i'), 'to' => $selected->shift_end_at->format('H:i')])">
                <x-slot:actions>
                    <a href="{{ route('logbook.days.show', $selected) }}" class="btn btn-primary btn-sm">{{ __('logbook::logbook.folders.open_day') }}</a>
                    <a href="{{ route('logbook.days.word', $selected) }}" class="btn btn-default btn-sm"><i class="feather icon-file-text"></i> {{ __('logbook::logbook.days.word') }}</a>
                </x-slot:actions>

                <div class="gs-tiles">
                    <div><small>{{ __('logbook::logbook.days.fields.status') }}</small><strong><x-ui.status-badge :status="$selected->status" /></strong></div>
                    <div><small>{{ __('logbook::logbook.days.counters.entries_total') }}</small><strong>{{ $counters['entries_total'] }}</strong></div>
                    <div><small>{{ __('logbook::logbook.days.counters.received') }} / {{ __('logbook::logbook.days.counters.sent') }}</small><strong>{{ $counters['received'] }} / {{ $counters['sent'] }}</strong></div>
                    <div><small>{{ __('logbook::logbook.days.counters.orders_or_decisions') }}</small><strong>{{ $counters['orders_or_decisions'] }}</strong></div>
                </div>

                @if ($folder === 'summary')
                    <div class="row">
                        <div class="col-md-4">
                            <h6>{{ __('logbook::logbook.folders.vessels') }}</h6>
                            @forelse ($content['vessels'] as $vessel)
                                <div><a href="{{ $link($selected->id, 'vessels', $vessel['id']) }}">{{ $vessel['label'] }}</a> <span class="gs-tree-count">{{ $vessel['count'] }}</span></div>
                            @empty
                                <p class="text-muted f-12">{{ __('logbook::logbook.folders.none') }}</p>
                            @endforelse
                        </div>
                        <div class="col-md-4">
                            <h6>{{ __('logbook::logbook.folders.actors') }}</h6>
                            @forelse ($content['actors'] as $actor)
                                <div><a href="{{ $link($selected->id, 'actors', $actor['id']) }}">{{ $actor['label'] }}</a> <span class="gs-tree-count">{{ $actor['count'] }}</span></div>
                            @empty
                                <p class="text-muted f-12">{{ __('logbook::logbook.folders.none') }}</p>
                            @endforelse
                        </div>
                        <div class="col-md-4">
                            <h6>{{ __('logbook::logbook.folders.operations') }}</h6>
                            @forelse ($content['operations'] as $operation)
                                <div><a href="{{ $link($selected->id, 'operations', $operation['id']) }}">{{ $operation['label'] }}</a> <span class="gs-tree-count">{{ $operation['count'] }}</span></div>
                            @empty
                                <p class="text-muted f-12">{{ __('logbook::logbook.folders.none') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </x-ui.card>

            @if ($folder === 'summary')
                @include('logbook::partials.timeline', ['entries' => $content['entries']])
            @else
                <x-ui.card :title="__('logbook::logbook.folders.'.$folder)" :subtitle="trans_choice('logbook::logbook.folders.entries_count', $content['filtered']->count(), ['count' => $content['filtered']->count()])" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('logbook::logbook.entries.fields.occurred_at') }}</th>
                            <th>{{ __('logbook::logbook.entries.fields.channel') }}</th>
                            <th>{{ __('logbook::logbook.entries.fields.content') }}</th>
                            <th>{{ __('logbook::logbook.entries.fields.vessels') }}</th>
                            <th>{{ __('logbook::logbook.folders.author') }}</th>
                        </x-slot:head>
                        @forelse ($content['filtered'] as $entry)
                            <tr>
                                <td class="text-nowrap">{{ $entry->occurred_at->format('H:i') }}</td>
                                <td>{{ $entry->channel->label }}<div class="text-muted f-12">{{ __($entry->direction->label()) }}</div></td>
                                <td class="text-wrap text-pre-line">{{ $entry->content }} @if ($entry->is_order_or_decision)<span class="label label-danger">{{ __('logbook::logbook.entries.order_or_decision') }}</span>@endif</td>
                                <td>@foreach ($entry->vessels as $vessel)<div><x-ui.vessel-link :vessel="$vessel" :thumb="false" /></div>@endforeach</td>
                                <td>{{ $entry->author?->fullName() }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            @endif
        </div>
    </div>
@endif
