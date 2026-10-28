@can(App\Support\Authorization\Permission::ReportsViewHealth->value)
    @inject('health', App\Support\Health\SystemHealth::class)
    @php
        $affectedSources = $health->affectedSources();
        $critical = $health->isCritical($affectedSources);
    @endphp
    @if ($affectedSources->isNotEmpty())
        <div @class(['alert', 'alert-danger background-danger' => $critical, 'alert-warning background-warning' => ! $critical]) role="status" data-health-banner>
            <i class="feather icon-activity m-r-5"></i>
            <strong>{{ __('ui.health.title') }}</strong>
            {{ trans_choice('ui.health.message', $affectedSources->count(), ['count' => $affectedSources->count()]) }}
            {{ $affectedSources->pluck('name')->implode(', ') }}.
            @can(App\Support\Authorization\Permission::ConnectorsView->value)
                <a href="{{ route('administration.connectors') }}" class="alert-link">{{ __('ui.health.details') }}</a>
            @endcan
        </div>
    @endif
@endcan
