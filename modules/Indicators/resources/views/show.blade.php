<x-layouts.app :title="__('indicators::indicators.names.'.$indicator)">
    <x-ui.page-header
        :title="__('indicators::indicators.names.'.$indicator)"
        :subtitle="__('indicators::indicators.descriptions.'.$indicator)"
        :breadcrumbs="[__('indicators::indicators.fleet.title') => route('fleet.index')]"
    />

    <div class="page-body">
        <x-ui.filters :action="route('indicators.show', $indicator)">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters->search" class="col-md-3" />
            <x-ui.filter-input name="from" type="date" :label="__('indicators::indicators.filters.from')" :value="$filters->from?->toDateString()" />
            <x-ui.filter-input name="to" type="date" :label="__('indicators::indicators.filters.to')" :value="$filters->to?->toDateString()" />
            <x-ui.filter-select name="zone_id" :label="__('indicators::indicators.filters.zone')" :options="$zones" :value="$filters->zoneId" />
            <x-ui.filter-select name="flag" :label="__('indicators::indicators.filters.flag')" :options="$flags" :value="$filters->flag" />
            <x-ui.filter-select name="segment" :label="__('indicators::indicators.filters.segment')" :options="$segments" :value="$filters->segment" />
            <x-ui.filter-select name="status" :label="__('indicators::indicators.filters.status')" :options="$statuses" :value="$filters->status" />
        </x-ui.filters>

        <x-ui.card :title="__('indicators::indicators.list_title')" :subtitle="trans_choice('ui.results_count', $rows->total())" flush>
            <x-ui.table :paginator="$rows">
                <x-slot:head>
                    <th>{{ __('indicators::indicators.columns.vessel') }}</th>
                    <th>{{ __('indicators::indicators.columns.status_light') }}</th>
                    <th>{{ __('indicators::indicators.columns.date') }}</th>
                    <th>{{ __('indicators::indicators.columns.detail') }}</th>
                    <th>{{ __('indicators::indicators.columns.follow_up') }}</th>
                    <th>{{ __('indicators::indicators.columns.module') }}</th>
                </x-slot:head>

                @forelse ($rows as $row)
                    <tr>
                        <td><x-ui.vessel-link :vessel="$row->vessel" /></td>
                        <td>
                            @if ($row->vessel->currentState)
                                <x-ui.status-badge :status="App\Enums\VesselStatusLight::from($row->vessel->currentState->status_light)" />
                            @endif
                        </td>
                        <td>{{ $row->date?->isoFormat('L LT') ?? '—' }}</td>
                        <td>{{ $row->detail ?? '—' }}</td>
                        <td>{{ $row->followUp ?? '—' }}</td>
                        <td>{{ $row->module ? __('indicators::indicators.modules.'.$row->module) : '—' }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
