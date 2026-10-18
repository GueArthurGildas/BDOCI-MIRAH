<x-layouts.app :title="__('tracking::tracking.zones.title')">
    <x-ui.page-header :title="__('tracking::tracking.zones.title')" :subtitle="__('tracking::tracking.zones.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('tracking.zones')">
            <x-ui.filter-field :label="__('tracking::tracking.zones.at')" class="col-md-4">
                <input type="datetime-local" name="at" value="{{ $at->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
            </x-ui.filter-field>
        </x-ui.filters>

        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('tracking::tracking.zones.chart_title')" :subtitle="__('tracking::tracking.zones.situation_at', ['time' => $at->isoFormat('L LT')])">
                    <x-ui.chart :config="$chart" />
                </x-ui.card>
            </div>
            <div class="col-xl-6">
                <x-ui.card :title="__('tracking::tracking.zones.table_title')" :subtitle="__('tracking::tracking.zones.freshness_hint', ['hours' => $freshnessHours])" flush>
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
