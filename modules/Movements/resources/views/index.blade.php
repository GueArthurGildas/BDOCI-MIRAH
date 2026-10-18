<x-layouts.app :title="__('movements::movements.title')">
    <x-ui.page-header :title="__('movements::movements.title')" :subtitle="__('movements::movements.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('movements.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('movements::movements.search_placeholder')" class="col-md-3" />
            <x-ui.filter-select name="direction" :label="__('movements::movements.fields.direction')" :options="App\Enums\MovementDirection::options()" :value="$filters['direction'] ?? null" />
            <x-ui.filter-select name="port" :label="__('movements::movements.fields.port')" :options="$ports" :value="$filters['port'] ?? null" />
            <x-ui.filter-input name="from" type="date" :label="__('movements::movements.fields.from')" :value="$filters['from'] ?? null" />
            <x-ui.filter-input name="to" type="date" :label="__('movements::movements.fields.to')" :value="$filters['to'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('movements::movements.list_title')" :subtitle="trans_choice('ui.results_count', $movements->total())" flush>
            @can(App\Support\Authorization\Permission::MovementsCreate->value)
                <x-slot:actions>
                    <a href="{{ route('movements.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('movements::movements.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table :paginator="$movements">
                <x-slot:head>
                    <th>{{ __('movements::movements.fields.occurred_at') }}</th>
                    <th>{{ __('movements::movements.fields.direction') }}</th>
                    <th>{{ __('movements::movements.fields.vessel') }}</th>
                    <th>{{ __('movements::movements.fields.port') }}</th>
                    <th>{{ __('movements::movements.fields.reason') }}</th>
                    <th>{{ __('movements::movements.fields.recorded_by') }}</th>
                </x-slot:head>

                @forelse ($movements as $movement)
                    <tr>
                        <td class="text-nowrap">{{ $movement->occurred_at->isoFormat('L LT') }}</td>
                        <td><x-ui.status-badge :status="$movement->direction" /></td>
                        <td><x-ui.vessel-link :vessel="$movement->vessel" /></td>
                        <td>{{ $movement->port->name }}</td>
                        <td>{{ $movement->reason }}</td>
                        <td>{{ $movement->recorder?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
