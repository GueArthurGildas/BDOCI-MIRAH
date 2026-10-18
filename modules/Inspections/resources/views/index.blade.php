<x-layouts.app :title="__('inspections::inspections.title')">
    <x-ui.page-header :title="__('inspections::inspections.title')" :subtitle="__('inspections::inspections.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('inspections.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('inspections::inspections.search_placeholder')" class="col-md-3" />
            <x-ui.filter-select name="type" :label="__('inspections::inspections.fields.type')" :options="App\Enums\InspectionType::options()" :value="$filters['type'] ?? null" />
            <x-ui.filter-select name="result" :label="__('inspections::inspections.fields.result')" :options="App\Enums\InspectionResult::options()" :value="$filters['result'] ?? null" />
            <x-ui.filter-input name="from" type="date" :label="__('inspections::inspections.fields.from')" :value="$filters['from'] ?? null" />
            <x-ui.filter-input name="to" type="date" :label="__('inspections::inspections.fields.to')" :value="$filters['to'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('inspections::inspections.list_title')" :subtitle="trans_choice('ui.results_count', $inspections->total())" flush>
            <x-ui.table :paginator="$inspections">
                <x-slot:head>
                    <th>{{ __('inspections::inspections.fields.reference') }}</th>
                    <th>{{ __('inspections::inspections.fields.inspected_at') }}</th>
                    <th>{{ __('inspections::inspections.fields.vessel') }}</th>
                    <th>{{ __('inspections::inspections.fields.type') }}</th>
                    <th>{{ __('inspections::inspections.fields.port') }}</th>
                    <th>{{ __('inspections::inspections.fields.result') }}</th>
                </x-slot:head>
                @forelse ($inspections as $inspection)
                    <tr>
                        <td><a href="{{ route('inspections.show', $inspection) }}" class="f-w-600">{{ $inspection->external_reference }}</a></td>
                        <td>{{ $inspection->inspected_at->isoFormat('L') }}</td>
                        <td><x-ui.vessel-link :vessel="$inspection->vessel" /></td>
                        <td>{{ __($inspection->type->label()) }}</td>
                        <td>{{ $inspection->port?->name ?? '—' }}</td>
                        <td><x-ui.status-badge :status="$inspection->result" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
