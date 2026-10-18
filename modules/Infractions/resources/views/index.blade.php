<x-layouts.app :title="__('infractions::infractions.title')">
    <x-ui.page-header :title="__('infractions::infractions.title')" :subtitle="__('infractions::infractions.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('infractions.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('infractions::infractions.search_placeholder')" class="col-md-3" />
            <x-ui.filter-select name="status" :label="__('infractions::infractions.fields.status')" :options="App\Enums\InfractionStatus::options()" :value="$filters['status'] ?? null" />
            <x-ui.filter-select name="type" :label="__('infractions::infractions.fields.type')" :options="$types" :value="$filters['type'] ?? null" />
            <x-ui.filter-input name="from" type="date" :label="__('infractions::infractions.fields.from')" :value="$filters['from'] ?? null" />
            <x-ui.filter-input name="to" type="date" :label="__('infractions::infractions.fields.to')" :value="$filters['to'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('infractions::infractions.list_title')" :subtitle="trans_choice('ui.results_count', $infractions->total())" flush>
            <x-ui.table :paginator="$infractions">
                <x-slot:head>
                    <th>{{ __('infractions::infractions.fields.reference') }}</th>
                    <th>{{ __('infractions::infractions.fields.detected_at') }}</th>
                    <th>{{ __('infractions::infractions.fields.vessel') }}</th>
                    <th>{{ __('infractions::infractions.fields.type') }}</th>
                    <th>{{ __('infractions::infractions.fields.status') }}</th>
                </x-slot:head>
                @forelse ($infractions as $infraction)
                    <tr>
                        <td><a href="{{ route('infractions.show', $infraction) }}" class="f-w-600">{{ $infraction->reference }}</a></td>
                        <td>{{ $infraction->detected_at->isoFormat('L') }}</td>
                        <td><x-ui.vessel-link :vessel="$infraction->vessel" /></td>
                        <td class="text-wrap">{{ $infraction->type->name }}</td>
                        <td><x-ui.status-badge :status="$infraction->status" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
