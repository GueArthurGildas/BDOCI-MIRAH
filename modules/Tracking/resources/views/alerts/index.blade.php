<x-layouts.app :title="__('tracking::tracking.alerts.title')">
    <x-ui.page-header :title="__('tracking::tracking.alerts.title')" :subtitle="__('tracking::tracking.alerts.subtitle')" />

    <div class="page-body">
        <x-ui.notion-hint current="alert" />

        <div class="row">
            @foreach (App\Enums\AlertStatus::cases() as $status)
                <div class="col-xl-4 col-md-6">
                    <a href="{{ route('tracking.alerts.index', ['status' => $status->value]) }}">
                        <x-ui.stat-card :label="__($status->label())" :value="$countsByStatus->get($status->value, 0)" icon="icon-alert-triangle" :color="$status->color()" />
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row">
            @foreach (App\Enums\AlertCategory::cases() as $category)
                <div class="col-xl-4 col-md-6">
                    <a href="{{ route('tracking.alerts.index', [...request()->except('page', 'category'), 'category' => $category->value]) }}" class="gs-widget-link" data-bs-toggle="tooltip" data-bs-title="{{ __('enums.descriptions.alert_category.'.$category->value) }}">
                        <x-ui.stat-card :label="__($category->label())" :value="Number::format($countsByCategory[$category->value] ?? 0)" :icon="$category->icon()" :color="$category->color()" compact />
                    </a>
                </div>
            @endforeach
        </div>

        @if ($filters['category'] ?? null)
            <p class="m-b-10">
                <span class="label label-{{ App\Enums\AlertCategory::from($filters['category'])->color() }}">{{ __('enums.alert_category.'.$filters['category']) }}</span>
                <a href="{{ route('tracking.alerts.index', request()->except('page', 'category')) }}" class="m-l-10 f-12">{{ __('tracking::tracking.alerts.all_categories') }}</a>
            </p>
        @endif

        <x-ui.card :title="__('tracking::tracking.alerts.list_title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('tracking::tracking.alerts.category') }}</th>
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
                        <td><x-ui.status-badge :status="$categories[$alert->id] ?? App\Enums\AlertCategory::Other" /></td>
                        <td><x-ui.vessel-link :vessel="$alert->vessel" /></td>
                        <td>{{ __($alert->type->label()) }}</td>
                        <td>{{ $alert->rule?->label() ?? '—' }}</td>
                        <td>{{ $alert->zone?->name ?? '—' }}</td>
                        <td class="text-nowrap">{{ $alert->triggered_at->isoFormat('L LT') }}</td>
                        <td><x-ui.status-badge :status="$alert->status" /></td>
                        <td class="text-end"><a href="{{ route('tracking.alerts.show', $alert) }}" class="btn btn-outline-primary btn-mini">{{ __('tracking::tracking.alerts.open') }}</a></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="8" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        {{ $alerts->links() }}
    </div>
</x-layouts.app>
