<x-layouts.app :title="__('licenses::licenses.title')">
    <x-ui.page-header :title="__('licenses::licenses.title')" :subtitle="__('licenses::licenses.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach (App\Enums\LicenseStatus::cases() as $status)
                <div class="col-xl-3 col-md-6">
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

        <x-ui.filters :action="route('licenses.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('licenses::licenses.search_placeholder')" class="col-md-5" />
            <x-ui.filter-select name="status" :label="__('licenses::licenses.fields.status')" :options="App\Enums\LicenseStatus::options()" :value="$filters['status'] ?? null" />
            <x-ui.filter-select name="type" :label="__('licenses::licenses.fields.type')" :options="App\Enums\LicenseType::options()" :value="$filters['type'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('licenses::licenses.list_title')" :subtitle="trans_choice('ui.results_count', $licenses->total())" flush>
            <x-ui.table :paginator="$licenses">
                <x-slot:head>
                    <th>{{ __('licenses::licenses.fields.number') }}</th>
                    <th>{{ __('licenses::licenses.fields.vessel') }}</th>
                    <th>{{ __('licenses::licenses.fields.type') }}</th>
                    <th>{{ __('licenses::licenses.fields.valid_from') }}</th>
                    <th>{{ __('licenses::licenses.fields.valid_until') }}</th>
                    <th>{{ __('licenses::licenses.fields.status') }}</th>
                </x-slot:head>

                @forelse ($licenses as $license)
                    <tr>
                        <td class="f-w-600">{{ $license->number }}</td>
                        <td><x-ui.vessel-link :vessel="$license->vessel" /></td>
                        <td>{{ __($license->type->label()) }}</td>
                        <td>{{ $license->valid_from->isoFormat('L') }}</td>
                        <td>{{ $license->valid_until->isoFormat('L') }}</td>
                        <td><x-ui.status-badge :status="$license->status" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
