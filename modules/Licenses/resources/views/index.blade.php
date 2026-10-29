<x-layouts.app :title="__('licenses::licenses.title')">
    <x-ui.page-header :title="__('licenses::licenses.title')" :subtitle="__('licenses::licenses.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach (App\Enums\LicenseStatus::cases() as $status)
                <div class="col-xl col-md-4">
                    <a href="{{ route('licenses.index', ['status' => $status->value]) }}">
                        <x-ui.stat-card
                            :label="__($status->label())"
                            :value="Number::format($countsByStatus[$status->value])"
                            icon="icon-file-text"
                            :color="$status->color()"
                        />
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row">
            @foreach ($tiers as $tier)
                <div class="col-md-4">
                    <a href="{{ route('licenses.index', ['expiring_after' => $tier['from'], 'expiring_within' => $tier['days']]) }}">
                        <x-ui.stat-card
                            :label="__('licenses::licenses.expiring_within', ['days' => $tier['days']])"
                            :value="Number::format($tier['count'])"
                            icon="icon-clock"
                            color="warning"
                            compact
                        />
                    </a>
                </div>
            @endforeach
        </div>

        <x-ui.card :title="__('licenses::licenses.list_title')" :subtitle="trans_choice('ui.results_count', $licenses->total())" flush>
            <x-slot:actions>
                @can(App\Support\Authorization\Permission::LicensesManage->value)
                    <a href="{{ route('licenses.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('licenses::licenses.new') }}
                    </a>
                @endcan
            </x-slot:actions>

            <x-ui.table-toolbar :action="route('licenses.index')" :paginator="$licenses">
                <x-ui.toolbar-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('licenses::licenses.search_placeholder')" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$licenses">
                <x-slot:head>
                    <th>{{ __('licenses::licenses.fields.number') }}</th>
                    <th>{{ __('licenses::licenses.fields.vessel') }}</th>
                    <th>{{ __('licenses::licenses.fields.segment') }}</th>
                    <th>{{ __('licenses::licenses.fields.issued_on') }}</th>
                    <th>{{ __('licenses::licenses.fields.expires_on') }}</th>
                    <th>{{ __('licenses::licenses.fields.status') }}</th>
                </x-slot:head>

                <x-slot:filters>
                    <th></th>
                    <th></th>
                    <x-ui.column-filter name="segment" form="list-filters" :options="App\Enums\LicenseSegment::options()" :value="$filters['segment'] ?? null" />
                    <th></th>
                    <th></th>
                    <x-ui.column-filter name="status" form="list-filters" :options="App\Enums\LicenseStatus::options()" :value="$filters['status'] ?? null" />
                </x-slot:filters>

                @forelse ($licenses as $license)
                    <tr>
                        <td class="f-w-600"><a href="{{ route('licenses.show', $license) }}">{{ $license->number }}</a></td>
                        <td><x-ui.vessel-link :vessel="$license->vessel" /></td>
                        <td>{{ $license->segment ? __($license->segment->label()) : '—' }}</td>
                        <td>{{ $license->issued_on?->isoFormat('L') ?? '—' }}</td>
                        <td>{{ $license->expires_on->isoFormat('L') }}</td>
                        <td><x-ui.status-badge :status="$license->standing" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
