<x-layouts.app :title="__('tracking::tracking.slots.detail_title')">
    <x-ui.page-header :title="__('tracking::tracking.slots.detail_title')" :subtitle="$slot->started_at->isoFormat('L LT').' → '.$slot->ended_at->isoFormat('LT')" :breadcrumbs="[__('tracking::tracking.slots.title') => route('tracking.slots.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-3">
                <x-ui.card :title="__('tracking::tracking.slots.zone_counts')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('tracking::tracking.zones.zone') }}</th>
                            <th>{{ __('tracking::tracking.zones.vessels') }}</th>
                        </x-slot:head>
                        @forelse ($zoneCounts as $count)
                            <tr><td>{{ $count->zone->name }}</td><td>{{ $count->vessels_count }}</td></tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('tracking::tracking.slots.locality_counts')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('tracking::tracking.slots.locality') }}</th>
                            <th>{{ __('tracking::tracking.zones.vessels') }}</th>
                        </x-slot:head>
                        @forelse ($localityCounts as $label => $count)
                            <tr><td>{{ $label }}</td><td>{{ $count }}</td></tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('tracking::tracking.slots.sweeps')">
                    @foreach ($slot->sweeps as $sweep)
                        <p class="m-b-5">{{ __('tracking::tracking.slots.sweep_summary', ['vessels' => $sweep->vessels_count, 'silent' => $sweep->non_emitting_count, 'anomalies' => $sweep->anomalies->count()]) }}</p>
                    @endforeach
                    @can(App\Support\Authorization\Permission::TrackingCreate->value)
                        <form method="POST" action="{{ route('tracking.slots.sweep', $slot) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-mini">{{ __('tracking::tracking.slots.run_sweep') }}</button>
                        </form>
                    @endcan
                </x-ui.card>
            </div>

            <div class="col-xl-9">
                <x-ui.card :title="__('tracking::tracking.slots.photo')" :subtitle="__('tracking::tracking.slots.photo_hint')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('tracking::tracking.alerts.vessel') }}</th>
                            <th>{{ __('tracking::tracking.beacons.last_emission') }}</th>
                            <th>{{ __('tracking::tracking.alerts.zone') }}</th>
                            <th>{{ __('tracking::tracking.slots.emission_test') }}</th>
                            <th>{{ __('tracking::tracking.slots.speed_activity_test') }}</th>
                            <th>{{ __('tracking::tracking.slots.trajectory_test') }}</th>
                            <th>{{ __('tracking::tracking.slots.result') }}</th>
                            <th>{{ __('tracking::tracking.slots.themis') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($positions as $position)
                            <tr>
                                <td><x-ui.vessel-link :vessel="$position->vessel" /></td>
                                <td class="text-nowrap">{{ $position->last_emission_at?->isoFormat('L LT') ?? __('tracking::tracking.beacons.never') }}</td>
                                <td>{{ $position->zone?->name ?? '—' }}</td>
                                @can(App\Support\Authorization\Permission::TrackingCreate->value)
                                    @foreach (['emission_test', 'speed_activity_test', 'trajectory_test'] as $test)
                                        <td>
                                            <select name="{{ $test }}" form="confirm-{{ $position->id }}" class="form-select form-select-sm" aria-label="{{ __('tracking::tracking.slots.'.$test) }}">
                                                @foreach ($testOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected($position->$test->value === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endforeach
                                    <td>
                                        <select name="result" form="confirm-{{ $position->id }}" class="form-select form-select-sm" aria-label="{{ __('tracking::tracking.slots.result') }}">
                                            <option value="">{{ __('tracking::tracking.slots.result_auto') }}</option>
                                            @foreach ($resultOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($position->result?->value === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @else
                                    @foreach (['emission_test', 'speed_activity_test', 'trajectory_test'] as $test)
                                        <td><x-ui.status-badge :status="$position->$test" /></td>
                                    @endforeach
                                    <td>@if ($position->result)<x-ui.status-badge :status="$position->result" />@endif</td>
                                @endcan
                                <td class="text-nowrap">
                                    @if ($themis->url($position->vessel))
                                        <a href="{{ $themis->url($position->vessel) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-mini">{{ __('tracking::tracking.slots.open_themis') }}</a>
                                    @elseif ($themis->identifier($position->vessel))
                                        <button type="button" class="btn btn-outline-primary btn-mini" onclick="navigator.clipboard.writeText('{{ $themis->identifier($position->vessel) }}')">{{ __('tracking::tracking.slots.copy_identifier') }}</button>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @can(App\Support\Authorization\Permission::TrackingCreate->value)
                                        <button type="submit" form="confirm-{{ $position->id }}" class="btn btn-primary btn-mini">{{ __('tracking::tracking.slots.confirm') }}</button>
                                    @endcan
                                    @if ($position->result === App\Enums\SlotResult::Suspect)
                                        @can(App\Support\Authorization\Permission::WatchCreate->value)
                                            <a href="{{ route('watch.observations.create', ['vessel_id' => $position->vessel_id, 'slot_position_id' => $position->id]) }}" class="btn btn-outline-danger btn-mini">{{ __('tracking::tracking.slots.create_observation') }}</a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="9" />
                        @endforelse
                    </x-ui.table>
                    @can(App\Support\Authorization\Permission::TrackingCreate->value)
                        @foreach ($positions as $position)
                            <form method="POST" action="{{ route('tracking.slots.confirm', [$slot, $position]) }}" id="confirm-{{ $position->id }}">@csrf</form>
                        @endforeach
                    @endcan
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
