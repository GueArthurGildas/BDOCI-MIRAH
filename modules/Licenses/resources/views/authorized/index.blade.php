<x-layouts.app :title="__('licenses::authorized.title')">
    <x-ui.page-header :title="__('licenses::authorized.title')" :subtitle="__('licenses::authorized.subtitle')" :breadcrumbs="[__('licenses::licenses.title') => route('licenses.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-md-4">
                <x-ui.stat-card :label="__('licenses::authorized.count')" :value="Number::format($rows->count())" icon="icon-check-circle" color="success" compact />
            </div>
            <div class="col-md-4">
                <x-ui.stat-card
                    :label="__('licenses::authorized.last_publication')"
                    :value="$lastPublication ? __('licenses::authorized.version', ['version' => $lastPublication->version]).' · '.$lastPublication->published_at->isoFormat('L') : __('licenses::authorized.never')"
                    icon="icon-clock"
                    color="info"
                    compact
                />
            </div>
            <div class="col-md-4">
                <x-ui.stat-card
                    :label="__('licenses::authorized.freshness')"
                    :value="$isStale ? __('licenses::authorized.stale') : __('licenses::authorized.up_to_date')"
                    icon="icon-refresh-cw"
                    :color="$isStale ? 'warning' : 'success'"
                    compact
                />
            </div>
        </div>

        <x-ui.card :title="__('licenses::authorized.list_title')" flush>
            <x-slot:actions>
                @can(App\Support\Authorization\Permission::LicensesPublish->value)
                    <form data-confirm="{{ __('licenses::confirm.publish') }}" data-confirm-label="{{ __('licenses::confirm.publish_yes') }}" method="POST" action="{{ route('licenses.authorized.publish') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-upload"></i> {{ __('licenses::authorized.publish') }}</button>
                    </form>
                @endcan
            </x-slot:actions>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('licenses::licenses.fields.vessel') }}</th>
                    <th>{{ __('licenses::licenses.fields.number') }}</th>
                    <th>{{ __('licenses::licenses.fields.segment') }}</th>
                    <th>{{ __('licenses::licenses.fields.expires_on') }}</th>
                    <th>{{ __('licenses::licenses.fields.zones') }}</th>
                    <th>{{ __('licenses::licenses.fields.gears') }}</th>
                </x-slot:head>
                @forelse ($rows as $license)
                    <tr>
                        <td><x-ui.vessel-link :vessel="$license->vessel" /></td>
                        <td><a href="{{ route('licenses.show', $license) }}">{{ $license->number }}</a></td>
                        <td>{{ $license->segment ? __($license->segment->label()) : '—' }}</td>
                        <td>{{ $license->expires_on->isoFormat('L') }}</td>
                        <td>{{ $license->zones->pluck('name')->join(', ') }}</td>
                        <td>{{ $license->gears->pluck('label')->join(', ') }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <x-ui.card :title="__('licenses::authorized.history_title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('licenses::authorized.version_column') }}</th>
                    <th>{{ __('licenses::authorized.published_at') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($publications as $publication)
                    <tr>
                        <td>{{ $publication->version }}</td>
                        <td>{{ $publication->published_at->isoFormat('L LT') }}</td>
                        <td class="text-end"><a href="{{ route('licenses.authorized.download', $publication) }}" class="btn btn-default btn-mini"><i class="feather icon-download"></i> {{ __('licenses::authorized.download') }}</a></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="3" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
