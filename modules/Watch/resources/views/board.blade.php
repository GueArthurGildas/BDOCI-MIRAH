<x-layouts.app :title="__('watch::watch.board.title')">
    <x-ui.page-header :title="__('watch::watch.board.title')" :subtitle="__('watch::watch.board.subtitle')" />

    <div class="page-body">
        <div class="row">
            <div class="col-md-4">
                <x-ui.stat-card :label="__('watch::watch.board.stats.watched')" :value="$entries->count()" icon="icon-alert-triangle" color="danger" />
            </div>
            <div class="col-md-4">
                <x-ui.stat-card :label="__('watch::watch.board.stats.observations')" :value="$observations->count()" icon="icon-eye" color="warning" />
            </div>
            <div class="col-md-4">
                <x-ui.stat-card :label="__('watch::watch.board.stats.relays')" :value="$relays->count()" icon="icon-repeat" color="info" />
            </div>
        </div>

        @if ($relays->isNotEmpty())
            <x-ui.card :title="__('watch::watch.board.relays_title')" flush>
                <x-ui.table>
                    <x-slot:head>
                        <th>{{ __('watch::watch.fields.vessel') }}</th>
                        <th>{{ __('watch::watch.fields.body') }}</th>
                        <th>{{ __('watch::watch.fields.from_team') }}</th>
                        <th></th>
                    </x-slot:head>
                    @foreach ($relays as $relay)
                        <tr>
                            <td>@if ($relay->observation?->vessel)<x-ui.vessel-link :vessel="$relay->observation->vessel" />@endif</td>
                            <td class="text-wrap">{{ $relay->observation?->body }}</td>
                            <td>{{ $relay->handover->rotation->team->name }}</td>
                            <td class="text-end">
                                @can(App\Support\Authorization\Permission::WatchCreate->value)
                                    <form method="POST" action="{{ route('watch.relays.acknowledge', $relay) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-mini">{{ __('watch::watch.board.acknowledge') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        @endif

        @if ($checkAlerts->isNotEmpty())
            <x-ui.card :title="__('watch::watch.board.check_alerts_title')" :subtitle="__('watch::watch.board.check_alerts_hint', ['threshold' => config('surveillance.watch.check_threshold')])" flush>
                <x-ui.table>
                    <x-slot:head>
                        <th>{{ __('watch::watch.fields.vessel') }}</th>
                        <th>{{ __('watch::watch.fields.checks_count') }}</th>
                        <th></th>
                    </x-slot:head>
                    @foreach ($checkAlerts as $counter)
                        <tr>
                            <td><x-ui.vessel-link :vessel="$counter->vessel" /></td>
                            <td>{{ $counter->checks_count }}</td>
                            <td class="text-end">
                                @can(App\Support\Authorization\Permission::WatchCreate->value)
                                    <form method="POST" action="{{ route('watch.board.enhanced-monitoring') }}">
                                        @csrf
                                        <input type="hidden" name="vessel_id" value="{{ $counter->vessel_id }}">
                                        <input type="hidden" name="origin" value="{{ App\Enums\WatchOrigin::MoreThan3Checks->value }}">
                                        <button type="submit" class="btn btn-warning btn-mini">{{ __('watch::watch.board.confirm_enhanced_monitoring') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        @endif

        <x-ui.card :title="__('watch::watch.board.watched_title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('watch::watch.fields.vessel') }}</th>
                    <th>{{ __('watch::watch.fields.origin') }}</th>
                    <th>{{ __('watch::watch.fields.started_at') }}</th>
                    <th>{{ __('watch::watch.fields.link') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($entries as $entry)
                    <tr>
                        <td><x-ui.vessel-link :vessel="$entry->vessel" /></td>
                        <td>{{ __($entry->origin->label()) }}</td>
                        <td class="text-nowrap">{{ $entry->started_at->isoFormat('L LT') }}</td>
                        <td>
                            @if ($entry->operation)
                                <a href="{{ route('operations.show', $entry->operation) }}">{{ $entry->operation->code }}</a>
                            @elseif ($entry->logbookEntry)
                                <a href="{{ route('logbook.days.show', $entry->logbookEntry->logbook_day_id) }}">{{ __('watch::watch.board.logbook_line') }}</a>
                            @endif
                        </td>
                        <td class="text-end">
                            @can(App\Support\Authorization\Permission::WatchManage->value)
                                <form method="POST" action="{{ route('watch.board.close', $entry) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-mini">{{ __('watch::watch.board.close') }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('watch::watch.board.instructions_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('watch::watch.fields.vessel') }}</th>
                            <th>{{ __('watch::watch.fields.body') }}</th>
                        </x-slot:head>
                        @forelse ($instructions as $instruction)
                            <tr>
                                <td>@if ($instruction->vessel)<x-ui.vessel-link :vessel="$instruction->vessel" />@else<span class="text-muted">{{ __('watch::watch.instructions.general') }}</span>@endif</td>
                                <td class="text-wrap text-pre-line">{{ $instruction->body }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-6">
                <x-ui.card :title="__('watch::watch.board.observations_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('watch::watch.fields.vessel') }}</th>
                            <th>{{ __('watch::watch.fields.body') }}</th>
                            <th>{{ __('watch::watch.fields.created_at') }}</th>
                        </x-slot:head>
                        @forelse ($observations as $observation)
                            <tr>
                                <td><x-ui.vessel-link :vessel="$observation->vessel" /></td>
                                <td class="text-wrap">{{ Str::limit($observation->body, 90) }}</td>
                                <td class="text-nowrap">{{ $observation->created_at->isoFormat('L LT') }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>
        </div>

        @canany([App\Support\Authorization\Permission::WatchCreate->value, App\Support\Authorization\Permission::WatchManage->value])
            <x-ui.card :title="__('watch::watch.board.add_title')">
                <form method="POST" action="{{ route('watch.board.director-list') }}">
                    @csrf
                    <x-ui.form.select name="vessel_id" :label="__('watch::watch.fields.vessel')" :options="$vessels" required />
                    <div class="row">
                        <div class="col-sm-9 offset-sm-3">
                            @can(App\Support\Authorization\Permission::WatchManage->value)
                                <button type="submit" class="btn btn-primary">{{ __('watch::watch.board.add_director_list') }}</button>
                            @endcan
                            @can(App\Support\Authorization\Permission::WatchCreate->value)
                                <button type="submit" formaction="{{ route('watch.board.enhanced-monitoring') }}" class="btn btn-warning m-l-10">{{ __('watch::watch.board.add_enhanced_monitoring') }}</button>
                            @endcan
                        </div>
                    </div>
                </form>
            </x-ui.card>
        @endcanany
    </div>
</x-layouts.app>
