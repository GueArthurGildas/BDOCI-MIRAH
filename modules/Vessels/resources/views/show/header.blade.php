<x-ui.card data-guide="header" :title="__('vessels::vessels.header.title')">
    <div class="row align-items-center">
        <div class="col-lg-3">
            <p class="text-muted f-12 m-b-5">{{ __('vessels::vessels.header.status_light') }}</p>
            <h4 class="m-0"><x-ui.status-badge :status="$assessment->level" /></h4>
            @if ($assessment->reasons)
                <ul class="list-unstyled m-t-10 m-b-0 f-12">
                    @foreach ($assessment->reasons as $reason)
                        <li>{{ __('vigilance.reasons.'.$reason['key'], $reason['params']) }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="col-lg-6">
            <x-ui.detail-list>
                <x-ui.detail :label="__('vessels::vessels.fields.flag')">{{ $vessel->flagName() }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.imo_number')">{{ $vessel->imo_number ?: '—' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.header.mmsi')">{{ $vessel->identifierValue(App\Enums\IdentifierType::Mmsi) ?: '—' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.call_sign')">{{ $vessel->call_sign ?: '—' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.registration_number')">{{ $vessel->registration_number ?: '—' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.owner')">{{ $vessel->owner ?: '—' }}</x-ui.detail>
                @foreach ([App\Enums\ActorRole::LocalAgent, App\Enums\ActorRole::Captain] as $role)
                    <x-ui.detail :label="__($role->label())">{{ $vessel->actors->first(fn ($actor) => $actor->role === $role && $actor->ended_on === null)?->holderName() ?: '—' }}</x-ui.detail>
                @endforeach
            </x-ui.detail-list>
        </div>
        <div class="col-lg-3">
            <p class="text-muted f-12 m-b-5">{{ __('vessels::vessels.header.updated_at') }}</p>
            <p class="m-0">{{ $vessel->currentState?->updated_at?->isoFormat('L LT') ?? '—' }}</p>
            @if ($themisUrl)
                <a href="{{ $themisUrl }}" target="_blank" rel="noopener" class="btn btn-default btn-sm m-t-10">
                    <i class="feather icon-external-link"></i> {{ __('vessels::vessels.header.themis') }}
                </a>
            @endif
        </div>
    </div>
</x-ui.card>
