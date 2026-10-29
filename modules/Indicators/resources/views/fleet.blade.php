<x-layouts.app :title="__('indicators::indicators.fleet.title')">
    <x-ui.page-header :title="__('indicators::indicators.fleet.title')" :subtitle="__('indicators::indicators.fleet.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('fleet.index')">
            <x-ui.filter-input name="from" type="date" :label="__('indicators::indicators.filters.from')" :value="$filters->from?->toDateString()" />
            <x-ui.filter-input name="to" type="date" :label="__('indicators::indicators.filters.to')" :value="$filters->to?->toDateString()" />
            <x-ui.filter-select name="zone_id" :label="__('indicators::indicators.filters.zone')" :options="$zones" :value="$filters->zoneId" />
            <x-ui.filter-select name="segment" :label="__('indicators::indicators.filters.segment')" :options="$segments" :value="$filters->segment" />
        </x-ui.filters>

        <div class="row">
            @foreach ($tiles as $tile)
                <div class="col-xl-4 col-md-6">
                    <a href="{{ route('indicators.show', ['indicator' => $tile['key']] + $query) }}">
                        <x-ui.stat-card
                            :label="__('indicators::indicators.names.'.$tile['key'])"
                            :value="Number::format($tile['count'])"
                            :icon="$tile['icon']"
                            :color="$tile['color']"
                            :footer="__('indicators::indicators.open_detail')"
                        />
                    </a>
                </div>
            @endforeach
        </div>

        <x-ui.card data-guide="distribution" :title="__('indicators::indicators.fleet.distribution')" :subtitle="__('indicators::indicators.fleet.distribution_hint')">
            <div class="row">
                @foreach (App\Enums\VesselStatusLight::cases() as $light)
                    <div class="col-md-3">
                        <a href="{{ route('indicators.show', ['indicator' => 'status_'.$light->value]) }}">
                            <span class="label label-{{ $light->color() }}">{{ __($light->label()) }}</span>
                            <span class="f-w-600 m-l-5">{{ Number::format($distribution[$light->value]) }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card data-guide="ranking" :title="__('indicators::indicators.fleet.ranking')" :subtitle="__('indicators::indicators.fleet.ranking_hint')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('indicators::indicators.columns.vessel') }}</th>
                    <th>{{ __('indicators::indicators.columns.status_light') }}</th>
                    <th>{{ __('indicators::indicators.columns.score') }}</th>
                </x-slot:head>
                @forelse ($ranking as $row)
                    <tr>
                        <td><x-ui.vessel-link :vessel="$row['vessel']" /></td>
                        <td><x-ui.status-badge :status="App\Enums\VesselStatusLight::from($row['vessel']->currentState->status_light)" /></td>
                        <td>
                            <span class="label label-{{ App\Support\Vessels\VigilanceScore::color($row['score']['level']) }}">{{ $row['score']['total'] }}</span>
                            {{ __('vigilance.score.levels.'.$row['score']['level']) }}
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="3" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
