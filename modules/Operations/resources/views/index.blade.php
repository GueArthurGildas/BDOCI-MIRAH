<x-layouts.app :title="__('operations::operations.title')">
    <x-ui.page-header :title="__('operations::operations.title')" :subtitle="__('operations::operations.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('operations::operations.list_title')" :subtitle="trans_choice('ui.results_count', $operations->total())" flush>
            @can(App\Support\Authorization\Permission::OperationsCreate->value)
                <x-slot:actions>
                    <a href="{{ route('operations.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('operations::operations.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table-toolbar :action="route('operations.index')" :paginator="$operations">
                <x-ui.toolbar-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('operations::operations.search_placeholder')" />
                <x-ui.toolbar-input name="year" type="number" :label="__('operations::operations.fields.year')" :value="$filters['year'] ?? null" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$operations">
                <x-slot:head>
                    <th>{{ __('operations::operations.fields.code') }}</th>
                    <th>{{ __('operations::operations.fields.name') }}</th>
                    <th>{{ __('operations::operations.fields.category') }}</th>
                    <th>{{ __('operations::operations.fields.vessels') }}</th>
                    <th>{{ __('operations::operations.fields.started_at') }}</th>
                    <th>{{ __('operations::operations.fields.status') }}</th>
                    <th></th>
                </x-slot:head>

                <x-slot:filters>
                    <th></th>
                    <th></th>
                    <x-ui.column-filter name="category" form="list-filters" :options="$categories" :value="$filters['category'] ?? null" />
                    <th></th>
                    <th></th>
                    <x-ui.column-filter name="status" form="list-filters" :options="App\Enums\OperationStatus::options()" :value="$filters['status'] ?? null" />
                    <th></th>
                </x-slot:filters>

                @forelse ($operations as $operation)
                    <tr>
                        <td class="text-nowrap">{{ $operation->code }}</td>
                        <td>{{ $operation->name }}</td>
                        <td>{{ __($operation->category->code->label()) }}</td>
                        <td>
                            @foreach ($operation->vessels as $vessel)
                                <div><x-ui.vessel-link :vessel="$vessel" /></div>
                            @endforeach
                        </td>
                        <td class="text-nowrap">{{ $operation->started_at->isoFormat('L LT') }}</td>
                        <td><x-ui.status-badge :status="$operation->status" /></td>
                        <td class="text-end">
                            <a href="{{ route('operations.show', $operation) }}" class="btn btn-outline-primary btn-mini">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
