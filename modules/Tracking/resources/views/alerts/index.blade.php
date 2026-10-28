<x-layouts.app :title="__('tracking::tracking.alerts.title')">
    <x-ui.page-header :title="__('tracking::tracking.alerts.title')" :subtitle="__('tracking::tracking.alerts.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach (App\Enums\AlertStatus::cases() as $status)
                <div class="col-xl-4 col-md-6">
                    <a href="{{ route('tracking.alerts.index', ['status' => $status->value]) }}">
                        <x-ui.stat-card :label="__($status->label())" :value="$countsByStatus->get($status->value, 0)" icon="icon-alert-triangle" :color="$status->color()" />
                    </a>
                </div>
            @endforeach
        </div>

        <x-ui.card :title="__('tracking::tracking.alerts.list_title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('tracking::tracking.alerts.vessel') }}</th>
                    <th>{{ __('tracking::tracking.alerts.type') }}</th>
                    <th>{{ __('tracking::tracking.alerts.rule') }}</th>
                    <th>{{ __('tracking::tracking.alerts.zone') }}</th>
                    <th>{{ __('tracking::tracking.alerts.triggered_at') }}</th>
                    <th>{{ __('tracking::tracking.alerts.status') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($alerts as $alert)
                    <tr>
                        <td><x-ui.vessel-link :vessel="$alert->vessel" /></td>
                        <td>{{ __($alert->type->label()) }}</td>
                        <td>{{ $alert->rule?->code ?? '—' }}</td>
                        <td>{{ $alert->zone?->name ?? '—' }}</td>
                        <td class="text-nowrap">{{ $alert->triggered_at->isoFormat('L LT') }}</td>
                        <td><x-ui.status-badge :status="$alert->status" /></td>
                        <td class="text-end"><a href="{{ route('tracking.alerts.show', $alert) }}" class="btn btn-outline-primary btn-mini">{{ __('tracking::tracking.alerts.open') }}</a></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        {{ $alerts->links() }}
    </div>
</x-layouts.app>
