<x-layouts.app :title="__('dashboard::dashboard.title')">
    <x-ui.page-header :title="__('dashboard::dashboard.title')" :subtitle="__('dashboard::dashboard.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach ($stats as $stat)
                <div class="col-xl-3 col-md-6">
                    <x-ui.stat-card
                        :label="__('dashboard::dashboard.stats.'.$stat['label'])"
                        :value="Number::format($stat['value'])"
                        :icon="$stat['icon']"
                        :color="$stat['color']"
                        :footer="__('ui.updated_at', ['time' => $now->isoFormat('LT')])"
                    />
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('dashboard::dashboard.zones_title')" :subtitle="__('dashboard::dashboard.zones_subtitle', ['time' => $now->isoFormat('LT')])">
                    <x-ui.chart :config="$zonesChart" height="260" />
                </x-ui.card>
            </div>
            <div class="col-xl-6">
                <x-ui.card :title="__('dashboard::dashboard.infractions_title')">
                    <x-ui.chart :config="$infractionsChart" height="260" />
                </x-ui.card>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-5">
                <x-ui.card :title="__('dashboard::dashboard.inspections_title')">
                    <x-ui.chart :config="$inspectionsChart" height="260" />
                </x-ui.card>
            </div>
            <div class="col-xl-7">
                <x-ui.card :title="__('dashboard::dashboard.sources_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('dashboard::dashboard.columns.source') }}</th>
                            <th>{{ __('dashboard::dashboard.columns.last_synced_at') }}</th>
                            <th class="text-end">{{ __('dashboard::dashboard.columns.errors') }}</th>
                            <th class="text-end">{{ __('dashboard::dashboard.columns.rejected') }}</th>
                            <th>{{ __('dashboard::dashboard.columns.status') }}</th>
                        </x-slot:head>
                        @foreach ($dataSources as $source)
                            <tr>
                                <td>{{ $source->name }}</td>
                                <td>{{ $source->last_synced_at?->diffForHumans() ?? __('dashboard::dashboard.never') }}</td>
                                <td class="text-end">{{ $source->open_errors_count }}</td>
                                <td class="text-end">{{ $source->pending_rejections_count }}</td>
                                <td><x-ui.status-badge :status="$source->indicator_status" /></td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </x-ui.card>
            </div>
        </div>

        <x-ui.card :title="__('dashboard::dashboard.observations_title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('dashboard::dashboard.columns.date') }}</th>
                    <th>{{ __('dashboard::dashboard.columns.level') }}</th>
                    <th>{{ __('dashboard::dashboard.columns.vessel') }}</th>
                    <th>{{ __('dashboard::dashboard.columns.description') }}</th>
                </x-slot:head>
                @forelse ($pendingObservations as $observation)
                    <tr>
                        <td class="text-nowrap">{{ $observation->observed_at->isoFormat('L LT') }}</td>
                        <td><x-ui.status-badge :status="$observation->level" /></td>
                        <td>
                            @if ($observation->vessel)
                                <x-ui.vessel-link :vessel="$observation->vessel" />
                            @endif
                        </td>
                        <td class="text-wrap">{{ Str::limit($observation->description, 120) }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="4" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
