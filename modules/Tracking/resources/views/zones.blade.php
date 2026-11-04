<x-layouts.app :title="__('tracking::tracking.zones.title')">
    <x-ui.page-header :title="__('tracking::tracking.zones.title')" :subtitle="__('tracking::tracking.zones.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('tracking.zones')">
            <x-ui.filter-field :label="__('tracking::tracking.zones.at')" class="col-md-3">
                <input type="datetime-local" name="at" value="{{ $at->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
            </x-ui.filter-field>
            <x-ui.filter-select name="last" :label="__('tracking::tracking.zones.quick_period')" :options="$periods" :value="$filters['last'] ?? null" class="col-md-3" />
            <x-ui.filter-field :label="__('tracking::tracking.zones.from')" class="col-md-3">
                <input type="datetime-local" name="from" value="{{ $from && ! isset($filters['last']) ? $from->format('Y-m-d\TH:i') : '' }}" class="form-control form-control-sm">
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('tracking::tracking.zones.to')" class="col-md-3">
                <input type="datetime-local" name="to" value="{{ $to && ! isset($filters['last']) && isset($filters['to']) ? $to->format('Y-m-d\TH:i') : '' }}" class="form-control form-control-sm">
            </x-ui.filter-field>
        </x-ui.filters>

        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('tracking::tracking.zones.chart_title')" :subtitle="$from && $to ? __('tracking::tracking.zones.situation_between', ['from' => $from->isoFormat('L LT'), 'to' => $to->isoFormat('L LT')]) : __('tracking::tracking.zones.situation_at', ['time' => $at->isoFormat('L LT')])">
                    <x-ui.chart :config="$chart" />
                </x-ui.card>
            </div>
            <div class="col-xl-6">
                <x-ui.card :title="__('tracking::tracking.zones.table_title')" :subtitle="$from && $to ? __('tracking::tracking.zones.period_hint') : __('tracking::tracking.zones.freshness_hint', ['hours' => $freshnessHours])" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('tracking::tracking.zones.zone') }}</th>
                            <th>{{ __('tracking::tracking.zones.type') }}</th>
                            <th class="text-end">{{ __('tracking::tracking.zones.vessels') }}</th>
                        </x-slot:head>
                        @foreach ($zones as $zone)
                            <tr>
                                <td class="text-wrap">{{ $zone->name }}</td>
                                <td class="text-wrap">{{ __($zone->type->label()) }}</td>
                                <td class="text-end f-w-600">{{ $vesselCountByZone->get($zone->id, 0) }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
