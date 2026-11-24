@if ($entry->observation)
    <div>
        @can(App\Support\Authorization\Permission::LogbookView->value)
            <a href="{{ route('logbook.show', $entry->observation) }}"><i class="feather icon-eye"></i> {{ __('logbook::logbook.entries.observation', ['id' => $entry->observation->id]) }}</a>
        @else
            <i class="feather icon-eye"></i> {{ __('logbook::logbook.entries.observation', ['id' => $entry->observation->id]) }}
        @endcan
        <x-ui.status-badge :status="$entry->observation->status" />
    </div>
@elseif ($entry->surveillanceObservation)
    <div>
        @can(App\Support\Authorization\Permission::WatchView->value)
            <a href="{{ route('watch.observations.index') }}"><i class="feather icon-search"></i> {{ __('logbook::logbook.entries.surveillance_observation', ['id' => $entry->surveillanceObservation->id]) }}</a>
        @else
            <i class="feather icon-search"></i> {{ __('logbook::logbook.entries.surveillance_observation', ['id' => $entry->surveillanceObservation->id]) }}
        @endcan
        <x-ui.status-badge :status="$entry->surveillanceObservation->status" />
    </div>
@endif
@if ($entry->witnessReport)
    <div>
        @can(App\Support\Authorization\Permission::WitnessesView->value)
            <a href="{{ route('witnesses.show', $entry->witnessReport) }}"><i class="feather icon-eye"></i> {{ __('logbook::logbook.entries.witness_report', ['id' => $entry->witnessReport->id]) }}</a>
        @else
            <i class="feather icon-eye"></i> {{ __('logbook::logbook.entries.witness_report', ['id' => $entry->witnessReport->id]) }}
        @endcan
        <x-ui.status-badge :status="$entry->witnessReport->status" />
    </div>
@endif
