<x-layouts.app :title="__('catches::catches.title')">
    <x-ui.page-header :title="__('catches::catches.title')" :subtitle="__('catches::catches.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('catches::catches.chart_title')" :subtitle="__('catches::catches.total', ['quantity' => __('catches::catches.kilograms', ['value' => Number::format($totalQuantity)])])">
            <x-ui.chart :config="$chart" height="260" />
        </x-ui.card>

        <x-ui.card :title="__('catches::catches.list_title')" :subtitle="trans_choice('ui.results_count', $declarations->total())" flush>
            <x-ui.table-toolbar :action="route('catches.index')" :paginator="$declarations">
                <x-ui.toolbar-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('catches::catches.search_placeholder')" />
                <x-ui.toolbar-input name="from" type="date" :label="__('catches::catches.fields.from')" :value="$filters['from'] ?? null" />
                <x-ui.toolbar-input name="to" type="date" :label="__('catches::catches.fields.to')" :value="$filters['to'] ?? null" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$declarations">
                <x-slot:head>
                    <th>{{ __('catches::catches.fields.period') }}</th>
                    <th>{{ __('catches::catches.fields.vessel') }}</th>
                    <th>{{ __('catches::catches.fields.species') }}</th>
                    <th>{{ __('catches::catches.fields.zone') }}</th>
                    <th class="text-end">{{ __('catches::catches.fields.quantity') }}</th>
                    <th>{{ __('catches::catches.fields.source') }}</th>
                </x-slot:head>

                <x-slot:filters>
                    <th></th>
                    <th></th>
                    <x-ui.column-filter name="species" form="list-filters" :options="$species" :value="$filters['species'] ?? null" />
                    <x-ui.column-filter name="zone" form="list-filters" :options="$zones" :value="$filters['zone'] ?? null" />
                    <th></th>
                    <x-ui.column-filter name="source" form="list-filters" :options="App\Enums\CatchSource::options()" :value="$filters['source'] ?? null" />
                </x-slot:filters>
                @forelse ($declarations as $declaration)
                    <tr>
                        <td class="text-nowrap">{{ $declaration->period_start->isoFormat('L') }} – {{ $declaration->period_end->isoFormat('L') }}</td>
                        <td><x-ui.vessel-link :vessel="$declaration->vessel" /></td>
                        <td>{{ $declaration->species->name }} <span class="text-muted f-12">({{ $declaration->species->fao_code }})</span></td>
                        <td>{{ $declaration->zone?->name ?? '—' }}</td>
                        <td class="text-end">{{ __('catches::catches.kilograms', ['value' => Number::format($declaration->quantity_kg)]) }}</td>
                        <td>{{ __($declaration->source->label()) }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
