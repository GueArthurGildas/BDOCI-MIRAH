<x-layouts.app :title="__('infractions::infractions.title')">
    <x-ui.page-header :title="__('infractions::infractions.title')" :subtitle="__('infractions::infractions.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('infractions.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('infractions::infractions.search_placeholder')" class="col-md-3" />
            <x-ui.filter-select name="status" :label="__('infractions::infractions.fields.status')" :options="App\Enums\InfractionCaseStatus::options()" :value="$filters['status'] ?? null" />
            <x-ui.filter-select name="type" :label="__('infractions::infractions.fields.type')" :options="$types" :value="$filters['type'] ?? null" />
            <x-ui.filter-input name="from" type="date" :label="__('infractions::infractions.fields.from')" :value="$filters['from'] ?? null" />
            <x-ui.filter-input name="to" type="date" :label="__('infractions::infractions.fields.to')" :value="$filters['to'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('infractions::infractions.list_title')" :subtitle="trans_choice('ui.results_count', $cases->total())" flush>
            <x-slot:actions>
                @can(App\Support\Authorization\Permission::InfractionsCreate->value)
                    <a href="{{ route('infractions.create') }}" class="btn btn-primary btn-sm"><i class="feather icon-plus"></i> {{ __('infractions::infractions.open_case') }}</a>
                @endcan
                @can(App\Support\Authorization\Permission::InfractionsManageTypes->value)
                    <a href="{{ route('infractions.types.index') }}" class="btn btn-default btn-sm"><i class="feather icon-list"></i> {{ __('infractions::infractions.nomenclature') }}</a>
                @endcan
            </x-slot:actions>
            <x-ui.table :paginator="$cases">
                <x-slot:head>
                    <th>{{ __('infractions::infractions.fields.reference') }}</th>
                    <th>{{ __('infractions::infractions.fields.opened_at') }}</th>
                    <th>{{ __('infractions::infractions.fields.vessel') }}</th>
                    <th>{{ __('infractions::infractions.fields.type') }}</th>
                    <th>{{ __('infractions::infractions.fields.qualification') }}</th>
                    <th>{{ __('infractions::infractions.fields.status') }}</th>
                </x-slot:head>
                @forelse ($cases as $case)
                    <tr>
                        <td><a href="{{ route('infractions.show', $case) }}" class="f-w-600">{{ $case->reference }}</a></td>
                        <td>{{ $case->opened_at->isoFormat('L') }}</td>
                        <td><x-ui.vessel-link :vessel="$case->vessel" /></td>
                        <td class="text-wrap">{{ $case->upheld->map(fn ($upheld) => $upheld->type->label)->implode(', ') ?: '—' }}</td>
                        <td><x-ui.status-badge :status="$case->qualification" /></td>
                        <td><x-ui.status-badge :status="$case->status" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
