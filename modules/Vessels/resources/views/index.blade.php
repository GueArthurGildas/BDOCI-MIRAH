<x-layouts.app :title="__('vessels::vessels.title')">
    <x-ui.page-header :title="__('vessels::vessels.title')" :subtitle="__('vessels::vessels.subtitle')" />

    <div class="page-body">
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

            <x-ui.table-toolbar :action="route('vessels.index')" :paginator="$vessels">
                <x-slot:exports>
                    @if (Route::has('reports.exports.download') && auth()->user()->can(App\Support\Authorization\Permission::ReportsExportVessels->value))
                        <a href="{{ route('reports.exports.download', ['type' => 'vessels', 'format' => 'csv', 'search' => $filters['search'] ?? null]) }}" class="btn btn-dark btn-sm">
                            <i class="feather icon-file-text"></i> {{ __('vessels::vessels.export_csv') }}
                        </a>
                        <a href="{{ route('reports.exports.download', ['type' => 'vessels', 'format' => 'pdf', 'search' => $filters['search'] ?? null]) }}" class="btn btn-dark btn-sm">
                            <i class="feather icon-file"></i> {{ __('vessels::vessels.export_pdf') }}
                        </a>
                    @endif
                </x-slot:exports>

                <x-ui.toolbar-select name="category" :label="__('vessels::vessels.fields.category')" :options="App\Enums\VesselCategory::options()" :value="$filters['category'] ?? null" />
                <x-ui.toolbar-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('vessels::vessels.search_placeholder')" />
            </x-ui.table-toolbar>

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

                <x-slot:filters>
                    <x-ui.column-filter name="name" form="list-filters" :value="$filters['name'] ?? null" />
                    <x-ui.column-filter name="registration" form="list-filters" :value="$filters['registration'] ?? null" />
                    <x-ui.column-filter name="flag" form="list-filters" :options="App\Support\Localization\Countries::options()" :value="$filters['flag'] ?? null" />
                    <th></th>
                    <th></th>
                    <th></th>
                    <x-ui.column-filter name="status" form="list-filters" :options="App\Enums\VesselStatus::options()" :value="$filters['status'] ?? null" />
                    <th></th>
                </x-slot:filters>

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
