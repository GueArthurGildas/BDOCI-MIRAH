<x-layouts.app :title="__('tracking::tracking.beacons.title')">
    <x-ui.page-header :title="__('tracking::tracking.beacons.title')" :subtitle="__('tracking::tracking.beacons.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach (App\Enums\BeaconStatus::cases() as $status)
                <div class="col-xl-4 col-md-6">
                    <a href="{{ route('tracking.beacons', ['status' => $status->value]) }}">
                        <x-ui.stat-card :label="__($status->label())" :value="$countsByStatus->get($status->value, 0)" icon="icon-radio" :color="$status->color()" />
                    </a>
                </div>
            @endforeach
        </div>

        <x-ui.card :title="__('tracking::tracking.beacons.list_title')" :subtitle="__('tracking::tracking.beacons.threshold_hint', ['hours' => $thresholdHours])" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('tracking::tracking.beacons.vessel') }}</th>
                    <th>{{ __('tracking::tracking.beacons.system') }}</th>
                    <th>{{ __('tracking::tracking.beacons.identifier') }}</th>
                    <th>{{ __('tracking::tracking.beacons.last_emission') }}</th>
                    <th>{{ __('tracking::tracking.beacons.zone') }}</th>
                    <th>{{ __('tracking::tracking.beacons.status') }}</th>
                </x-slot:head>
                @forelse ($devices as $device)
                    @php($lastPosition = $device->vessel->latestPosition)
                    <tr>
                        <td><x-ui.vessel-link :vessel="$device->vessel" /></td>
                        <td><x-ui.status-badge :status="$device->system" /></td>
                        <td>{{ $device->identifier }}</td>
                        <td>
                            @if ($lastPosition)
                                {{ $lastPosition->recorded_at->isoFormat('L LT') }}
                                <div class="text-muted f-12">{{ $lastPosition->recorded_at->diffForHumans() }}</div>
                            @else
                                {{ __('tracking::tracking.beacons.never') }}
                            @endif
                        </td>
                        <td>{{ $lastPosition?->zone?->name ?? '—' }}</td>
                        <td><x-ui.status-badge :status="$device->vessel->beaconStatus()" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
