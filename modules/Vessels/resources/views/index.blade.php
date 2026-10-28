<x-layouts.app :title="__('vessels::vessels.title')">
    <x-ui.page-header :title="__('vessels::vessels.title')" :subtitle="__('vessels::vessels.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('vessels.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('vessels::vessels.search_placeholder')" class="col-md-4" />
            <x-ui.filter-select name="category" :label="__('vessels::vessels.fields.category')" :options="App\Enums\VesselCategory::options()" :value="$filters['category'] ?? null" />
            <x-ui.filter-select name="status" :label="__('vessels::vessels.fields.status')" :options="App\Enums\VesselStatus::options()" :value="$filters['status'] ?? null" />
            <x-ui.filter-select name="flag" :label="__('vessels::vessels.fields.flag')" :options="App\Support\Localization\Countries::options()" :value="$filters['flag'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('vessels::vessels.list_title')" :subtitle="trans_choice('ui.results_count', $vessels->total())" flush>
            @can(App\Support\Authorization\Permission::VesselsManage->value)
                <x-slot:actions>
                    <a href="{{ route('vessels.ingest.create') }}" class="btn btn-default btn-sm">
                        <i class="feather icon-download"></i> {{ __('vessels::vessels.ingest.link') }}
                    </a>
                    <a href="{{ route('vessels.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('vessels::vessels.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table :paginator="$vessels">
                <x-slot:head>
                    <th>{{ __('vessels::vessels.fields.name') }}</th>
                    <th>{{ __('vessels::vessels.fields.registration_number') }}</th>
                    <th>{{ __('vessels::vessels.fields.flag') }}</th>
                    <th>{{ __('vessels::vessels.fields.type') }}</th>
                    <th>{{ __('vessels::vessels.fields.license') }}</th>
                    <th>{{ __('vessels::vessels.fields.beacon') }}</th>
                    <th>{{ __('vessels::vessels.fields.status') }}</th>
                    <th>{{ __('vessels::vessels.fields.record_status') }}</th>
                </x-slot:head>

                @forelse ($vessels as $vessel)
                    <tr>
                        <td>
                            <a href="{{ route('vessels.show', $vessel) }}" class="f-w-600">{{ $vessel->name }}</a>
                            <div class="text-muted f-12">{{ __($vessel->category->label()) }}</div>
                        </td>
                        <td>{{ $vessel->registration_number }}</td>
                        <td>{{ $vessel->flagName() }}</td>
                        <td>{{ $vessel->type ? __($vessel->type->label()) : '—' }}</td>
                        <td>
                            @if ($vessel->currentLicense)
                                <x-ui.status-badge :status="$vessel->currentLicense->standing" />
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><x-ui.status-badge :status="$vessel->beaconStatus()" /></td>
                        <td><x-ui.status-badge :status="$vessel->status" /></td>
                        <td><x-ui.status-badge :status="$vessel->record_status" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="8" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
