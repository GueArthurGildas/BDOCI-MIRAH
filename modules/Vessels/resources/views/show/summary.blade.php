<x-ui.card :title="$vessel->name" :subtitle="$vessel->flagName()">
    @can(App\Support\Authorization\Permission::VesselsManage->value)
        <x-slot:actions>
            <a href="{{ route('vessels.edit', $vessel) }}" class="btn btn-primary btn-sm">
                <i class="feather icon-edit"></i> {{ __('ui.edit') }}
            </a>
        </x-slot:actions>
    @endcan

    <x-ui.detail-list>
        <x-ui.detail :label="__('vessels::vessels.fields.category')"><x-ui.status-badge :status="$vessel->category" /></x-ui.detail>
        <x-ui.detail :label="__('vessels::vessels.fields.status')"><x-ui.status-badge :status="$vessel->status" /></x-ui.detail>
        <x-ui.detail :label="__('vessels::vessels.fields.license')">
            @if ($vessel->currentLicense)
                <x-ui.status-badge :status="$vessel->currentLicense->standing" />
                <span class="text-muted f-12 m-l-5">{{ $vessel->currentLicense->expires_on->isoFormat('L') }}</span>
            @else
                {{ __('vessels::vessels.summary.no_license') }}
            @endif
        </x-ui.detail>
        <x-ui.detail :label="__('vessels::vessels.fields.beacon')"><x-ui.status-badge :status="$vessel->beaconStatus()" /></x-ui.detail>
        <x-ui.detail :label="__('vessels::vessels.fields.type')">{{ __($vessel->type->label()) }}</x-ui.detail>
        <x-ui.detail :label="__('vessels::vessels.fields.owner')">{{ $vessel->owner }}</x-ui.detail>
    </x-ui.detail-list>
</x-ui.card>
