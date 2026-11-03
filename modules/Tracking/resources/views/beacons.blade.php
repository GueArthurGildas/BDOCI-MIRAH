<x-layouts.app :title="__('tracking::tracking.beacons.title')">
    <x-ui.page-header :title="__('tracking::tracking.beacons.title')" :subtitle="__('tracking::tracking.beacons.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach (App\Enums\BeaconStatus::cases() as $status)
                <div class="col-xl-4 col-md-6">
                    <a href="{{ route('tracking.beacons', [...request()->except('page', 'status'), 'status' => $status->value]) }}" class="gs-widget-link">
                        <x-ui.stat-card :label="__($status->label())" :value="Number::format($countsByStatus->get($status->value, 0))" :icon="$status->icon()" :color="$status->color()" />
                    </a>
                </div>
            @endforeach
        </div>

        <x-ui.card :title="__('tracking::tracking.beacons.list_title')" :subtitle="trans_choice('ui.results_count', $devices->total())" flush>
            <x-ui.table-toolbar :action="route('tracking.beacons')" :paginator="$devices">
                <x-ui.toolbar-input type="search" name="search" :label="__('tracking::tracking.beacons.search')" :value="$filters['search'] ?? null" />
                <x-ui.toolbar-select name="status" :label="__('tracking::tracking.beacons.status')" :options="App\Enums\BeaconStatus::options()" :value="$filters['status'] ?? null" />
                <x-ui.toolbar-select name="silent_for" :label="__('tracking::tracking.beacons.silent_for')" :options="collect($presets)->map(fn ($label) => __('tracking::tracking.beacons.silent_for').' '.$label)->all()" :value="$filters['silent_for'] ?? null" />
                <x-ui.toolbar-input type="date" name="from" :label="__('tracking::tracking.beacons.emitted_from')" :value="$filters['from'] ?? null" />
                <x-ui.toolbar-input type="date" name="to" :label="__('tracking::tracking.beacons.emitted_to')" :value="$filters['to'] ?? null" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$devices">
                <x-slot:head>
                    <th>{{ __('tracking::tracking.beacons.status') }}</th>
                    <th>{{ __('tracking::tracking.beacons.vessel') }}</th>
                    <th>{{ __('tracking::tracking.beacons.system') }}</th>
                    <th>{{ __('tracking::tracking.beacons.identifier') }}</th>
                    <th>{{ __('tracking::tracking.beacons.last_emission') }}</th>
                    <th>{{ __('tracking::tracking.beacons.gap') }}</th>
                    <th>{{ __('tracking::tracking.beacons.zone') }}</th>
                </x-slot:head>
                @forelse ($devices as $device)
                    @php($state = App\Enums\BeaconStatus::from($device->beacon_state))
                    <tr>
                        <td class="text-nowrap">
                            <span class="gs-state-icon is-{{ $state->color() }}" data-bs-toggle="tooltip" data-bs-title="{{ __('tracking::tracking.beacons.meaning.'.$state->value) }}"><i class="feather {{ $state->icon() }}"></i></span>
                            <x-ui.status-badge :status="$state" />
                        </td>
                        <td><x-ui.vessel-link :vessel="$device->vessel" /></td>
                        <td><x-ui.status-badge :status="$device->system" /></td>
                        <td>{{ $device->identifier }}</td>
                        <td>
                            @if ($device->last_emission_at)
                                {{ $device->last_emission_at->isoFormat('L LT') }}
                            @else
                                {{ __('tracking::tracking.beacons.never') }}
                            @endif
                        </td>
                        <td><strong class="gs-gap is-{{ $state->color() }}">{{ App\Support\Vms\EmissionGap::format($device->last_emission_at) }}</strong></td>
                        <td>{{ $zoneNames->get($device->last_zone_id, '—') }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <x-ui.card :title="__('tracking::tracking.beacons.how_title')">
            <ul class="mb-0">
                <li>{{ __('tracking::tracking.beacons.how_source') }}</li>
                <li>{{ __('tracking::tracking.beacons.how_emission') }}</li>
                <li>{{ __('tracking::tracking.beacons.how_silent', ['hours' => $thresholdHours]) }}</li>
                <li>{{ __('tracking::tracking.beacons.how_authorized') }}</li>
                <li>{{ __('tracking::tracking.beacons.how_alerts') }}</li>
            </ul>
        </x-ui.card>
    </div>
</x-layouts.app>
