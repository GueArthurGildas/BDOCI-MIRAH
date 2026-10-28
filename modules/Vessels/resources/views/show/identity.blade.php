<x-ui.card :title="__('vessels::vessels.tabs.identity')">
    <div class="row">
        <div class="col-lg-6">
            <x-ui.detail-list>
                <x-ui.detail :label="__('vessels::vessels.fields.name')">{{ $vessel->name }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.registration_number')">{{ $vessel->registration_number }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.imo_number')">{{ $vessel->imo_number }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.call_sign')">{{ $vessel->call_sign }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.flag')">{{ $vessel->flagName() }}</x-ui.detail>
            </x-ui.detail-list>
        </div>
        <div class="col-lg-6">
            <x-ui.detail-list>
                <x-ui.detail :label="__('vessels::vessels.fields.type')">{{ $vessel->type ? __($vessel->type->label()) : '' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.owner')">{{ $vessel->owner }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.length_meters')">{{ $vessel->length_meters ? __('vessels::vessels.units.meters', ['value' => Number::format($vessel->length_meters, 2)]) : '' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.gross_tonnage')">{{ $vessel->gross_tonnage ? Number::format($vessel->gross_tonnage, 2) : '' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.status')"><x-ui.status-badge :status="$vessel->status" /></x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.record_status')"><x-ui.status-badge :status="$vessel->record_status" /></x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.fleet_segment')">{{ $vessel->fleet_segment ? __($vessel->fleet_segment->label()) : '' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.legal_status')">{{ $vessel->legal_status ? __($vessel->legal_status->label()) : '' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.home_port_id')">{{ $vessel->homePort?->name }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.fields.construction_year')">{{ $vessel->construction_year }}</x-ui.detail>
            </x-ui.detail-list>
        </div>
    </div>
</x-ui.card>
