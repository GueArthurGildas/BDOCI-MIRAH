<x-layouts.app :title="__('movements::movements.title')">
    <x-ui.page-header :title="__('movements::movements.title')" :subtitle="__('movements::movements.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('movements.index')">
            <x-ui.filter-field :label="__('ui.search')" class="col-md-3">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ __('movements::movements.search_placeholder') }}">
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('movements::movements.fields.direction')">
                <select name="direction" class="form-select form-select-sm">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach (App\Enums\MovementDirection::options() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['direction'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('movements::movements.fields.port')">
                <select name="port" class="form-select form-select-sm">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach ($ports as $id => $name)
                        <option value="{{ $id }}" @selected((string) ($filters['port'] ?? '') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('movements::movements.fields.from')">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('movements::movements.fields.to')">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
            </x-ui.filter-field>
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
