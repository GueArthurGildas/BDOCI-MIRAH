<x-layouts.app :title="__('movements::eez.title')">
    <x-ui.page-header :title="__('movements::eez.title')" :subtitle="__('movements::eez.subtitle')" />

    <div class="page-body">
        @include('movements::eez._tabs')

        <x-ui.card :title="__('movements::eez.list_title')" :subtitle="trans_choice('ui.results_count', $declarations->total())" flush>
            <x-slot:actions>
                <a href="{{ route('movements.index') }}" class="btn btn-default btn-sm">
                    <i class="feather icon-anchor"></i> {{ __('movements::eez.to_port_movements') }}
                </a>
                @can(App\Support\Authorization\Permission::MovementsDeclare->value)
                    <a href="{{ route('movements.eez.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('movements::eez.new') }}
                    </a>
                @endcan
            </x-slot:actions>

            <x-ui.table-toolbar :action="route('movements.eez.index')" :paginator="$declarations">
                <x-ui.toolbar-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('movements::eez.search_placeholder')" />
                <x-ui.toolbar-input name="from" type="date" :label="__('movements::eez.fields.from')" :value="$filters['from'] ?? null" />
                <x-ui.toolbar-input name="to" type="date" :label="__('movements::eez.fields.to')" :value="$filters['to'] ?? null" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$declarations">
                <x-slot:head>
                    <th>{{ __('movements::eez.fields.received_at') }}</th>
                    <th>{{ __('movements::eez.fields.type') }}</th>
                    <th>{{ __('movements::eez.fields.vessel') }}</th>
                    <th>{{ __('movements::eez.fields.reason') }}</th>
                    <th class="text-end">{{ __('movements::eez.fields.total_kg') }}</th>
                    <th>{{ __('movements::eez.fields.entry_origin') }}</th>
                    <th>{{ __('movements::eez.fields.status') }}</th>
                    <th></th>
                </x-slot:head>

                <x-slot:filters>
                    <th></th>
                    <x-ui.column-filter name="type" form="list-filters" :options="App\Enums\EezDeclarationType::options()" :value="$filters['type'] ?? null" />
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <x-ui.column-filter name="status" form="list-filters" :options="App\Enums\EezDeclarationStatus::options()" :value="$filters['status'] ?? null" />
                    <th></th>
                </x-slot:filters>

                @forelse ($declarations as $declaration)
                    <tr>
                        <td class="text-nowrap">{{ $declaration->received_at->isoFormat('L LT') }}</td>
                        <td><x-ui.status-badge :status="$declaration->type" /></td>
                        <td>
                            @if ($declaration->vessel)
                                <x-ui.vessel-link :vessel="$declaration->vessel" />
                            @else
                                {{ $declaration->declared_name }}
                                <div class="text-muted f-12">{{ __('movements::eez.unidentified') }}</div>
                            @endif
                        </td>
                        <td>{{ $declaration->reason ? __($declaration->reason->label()) : '—' }}</td>
                        <td class="text-end">{{ number_format((float) $declaration->catches_sum_weight_kg, 0, ',', ' ') }}</td>
                        <td>{{ __($declaration->entry_origin->label()) }}</td>
                        <td><x-ui.status-badge :status="$declaration->status" /></td>
                        <td class="text-end"><a href="{{ route('movements.eez.show', $declaration) }}" class="btn btn-default btn-sm"><i class="feather icon-eye"></i></a></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="8" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
