<div class="gs-quick">
    <div class="gs-quick-head">
        <span class="gs-modal-icon"><i class="feather icon-anchor"></i></span>
        <div>
            <h5>{{ $vessel->name }}</h5>
            <small>{{ $vessel->registration_number }} · {{ $vessel->flagName() }}</small>
        </div>
        <x-ui.status-badge :status="$assessment->level" class="ms-auto" />
    </div>

    @if ($assessment->reasons)
        <ul class="gs-quick-reasons">
            @foreach ($assessment->reasons as $reason)
                <li>{{ __('vigilance.reasons.'.$reason['key'], $reason['params']) }}</li>
            @endforeach
        </ul>
    @endif

    <div class="gs-tiles">
        <div><small>{{ __('vessels::vessels.fields.category') }}</small><strong><x-ui.status-badge :status="$vessel->category" /></strong></div>
        <div><small>{{ __('vessels::vessels.fields.status') }}</small><strong><x-ui.status-badge :status="$vessel->status" /></strong></div>
        <div>
            <small>{{ __('vessels::vessels.fields.license') }}</small>
            <strong>
                @if ($vessel->currentLicense)
                    <x-ui.status-badge :status="$vessel->currentLicense->standing" /> <span class="text-muted f-12">{{ $vessel->currentLicense->expires_on->isoFormat('L') }}</span>
                @else
                    {{ __('vessels::vessels.summary.no_license') }}
                @endif
            </strong>
        </div>
        <div><small>{{ __('vessels::vessels.fields.beacon') }}</small><strong><x-ui.status-badge :status="$vessel->beaconStatus()" /></strong></div>
        <div><small>{{ __('vessels::vessels.fields.imo_number') }}</small><strong>{{ $vessel->imo_number ?: '—' }}</strong></div>
        <div><small>{{ __('vessels::vessels.header.mmsi') }}</small><strong>{{ $vessel->identifierValue(App\Enums\IdentifierType::Mmsi) ?: '—' }}</strong></div>
        <div>
            <small>{{ __('vessels::vessels.summary.last_position') }}</small>
            <strong>
                @if ($vessel->latestPosition)
                    {{ $vessel->latestPosition->zone?->name ?? '—' }} <span class="text-muted f-12">{{ $vessel->latestPosition->recorded_at->diffForHumans() }}</span>
                @else
                    {{ __('vessels::vessels.summary.no_position') }}
                @endif
            </strong>
        </div>
        <div>
            <small>{{ __('vessels::vessels.quick.vigilance') }}</small>
            <strong>
                <span class="label label-{{ App\Support\Vessels\VigilanceScore::color($score['level']) }}">{{ $score['total'] }} / {{ config('vigilance.score.maximum') }}</span>
                <span class="text-muted f-12">{{ __('vigilance.score.levels.'.$score['level']) }}</span>
            </strong>
        </div>
    </div>

    <div class="gs-modal-actions">
        <button type="button" class="btn btn-link" data-bs-dismiss="modal">{{ __('ui.close') }}</button>
        <a href="{{ route('vessels.show', $vessel) }}" class="btn btn-primary">{{ __('vessels::vessels.quick.open_profile') }}</a>
    </div>
</div>
