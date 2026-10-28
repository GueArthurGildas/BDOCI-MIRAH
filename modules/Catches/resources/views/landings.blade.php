<x-layouts.app :title="__('catches::catches.landings.title')">
    <x-ui.page-header :title="__('catches::catches.landings.title')" :subtitle="__('catches::catches.landings.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('catches.landings')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('catches::catches.search_placeholder')" class="col-md-3" />
            <x-ui.filter-input name="from" type="date" :label="__('catches::catches.fields.from')" :value="$filters['from'] ?? null" />
            <x-ui.filter-input name="to" type="date" :label="__('catches::catches.fields.to')" :value="$filters['to'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('catches::catches.landings.title')" :subtitle="trans_choice('ui.results_count', $landings->total())" flush>
            <x-ui.table :paginator="$landings">
                <x-slot:head>
                    <th>{{ __('catches::catches.mirror.date') }}</th>
                    <th>{{ __('catches::catches.mirror.vessel') }}</th>
                    <th>{{ __('catches::catches.mirror.port') }}</th>
                    <th>{{ __('catches::catches.mirror.origin') }}</th>
                    <th>{{ __('catches::catches.mirror.lines') }}</th>
                </x-slot:head>
                @forelse ($landings as $row)
                    <tr>
                        <td class="text-nowrap">{{ $row->landed_at->isoFormat('L LT') }}</td>
                        <td><x-ui.vessel-link :vessel="$row->vessel" /></td>
                        <td>{{ $row->port?->name ?? '—' }}</td>
                        <td>{{ __($row->origin->label()) }}</td>
                        <td>
                            @foreach ($row->lines ?? [] as $line)
                                <div>{{ $line['species'] ?? '' }} : {{ __('catches::catches.kilograms', ['value' => Number::format($line['weight_kg'] ?? 0)]) }}</div>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
