<x-layouts.app :title="__('inspections::inspections.title')">
    <x-ui.page-header :title="__('inspections::inspections.title')" :subtitle="__('inspections::inspections.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('inspections.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('inspections::inspections.search_placeholder')" class="col-md-3" />
            <x-ui.filter-select name="location_type" :label="__('inspections::inspections.fields.location_type')" :options="App\Enums\InspectionLocationType::options()" :value="$filters['location_type'] ?? null" />
            <x-ui.filter-select name="result" :label="__('inspections::inspections.fields.result')" :options="App\Enums\InspectionResult::options()" :value="$filters['result'] ?? null" />
            <x-ui.filter-input name="from" type="date" :label="__('inspections::inspections.fields.from')" :value="$filters['from'] ?? null" />
            <x-ui.filter-input name="to" type="date" :label="__('inspections::inspections.fields.to')" :value="$filters['to'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('inspections::inspections.list_title')" :subtitle="trans_choice('ui.results_count', $inspections->total())" flush>
            <x-ui.table :paginator="$inspections">
                <x-slot:head>
                    <th>{{ __('inspections::inspections.fields.reference') }}</th>
                    <th>{{ __('inspections::inspections.fields.started_at') }}</th>
                    <th>{{ __('inspections::inspections.fields.vessel') }}</th>
                    <th>{{ __('inspections::inspections.fields.location_type') }}</th>
                    <th>{{ __('inspections::inspections.fields.port') }}</th>
                    <th>{{ __('inspections::inspections.fields.result') }}</th>
                    <th class="text-end">{{ __('inspections::inspections.fields.infractions') }}</th>
                    <th class="text-end">{{ __('inspections::inspections.fields.exceeding_catches') }}</th>
                </x-slot:head>
                @forelse ($inspections as $inspection)
                    <tr>
                        <td><a href="{{ route('inspections.show', $inspection) }}" class="f-w-600">{{ $inspection->reference ?? $inspection->external_id }}</a></td>
                        <td>{{ $inspection->started_at?->isoFormat('L') ?? '—' }}</td>
                        <td>
                            @if ($inspection->vessel)
                                <x-ui.vessel-link :vessel="$inspection->vessel" />
                            @else
                                <span class="label label-warning">{{ __('inspections::inspections.unmatched') }}</span>
                            @endif
                        </td>
                        <td>{{ $inspection->location_type ? __($inspection->location_type->label()) : '—' }}</td>
                        <td>{{ $inspection->port?->name ?? '—' }}</td>
                        <td>
                            @if ($inspection->resultCase())
                                <x-ui.status-badge :status="$inspection->resultCase()" />
                            @else
                                {{ $inspection->resultLabel() }}
                            @endif
                        </td>
                        <td class="text-end">{{ $inspection->infractions_count }}</td>
                        <td class="text-end"><span @class(['f-w-600', 'text-danger' => $inspection->exceeding_catches_count > 0])>{{ $inspection->exceeding_catches_count }}</span></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="8" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
