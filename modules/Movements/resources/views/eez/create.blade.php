<x-layouts.app :title="__('movements::eez.create_title')">
    <x-ui.page-header :title="__('movements::eez.create_title')" :breadcrumbs="[__('movements::eez.title') => route('movements.eez.index')]" />

    <div class="page-body">
        <form method="POST" action="{{ route('movements.eez.store') }}">
            @csrf
            <x-ui.card :title="__('movements::eez.create_title')">
                <x-ui.form.select name="type" :label="__('movements::eez.fields.type')" :options="App\Enums\EezDeclarationType::options()" required />
                <x-ui.form.input name="received_at" type="datetime-local" :label="__('movements::eez.fields.received_at')" :value="now()->format('Y-m-d\TH:i')" required />
                <x-ui.form.select name="reception_channel_id" :label="__('movements::eez.fields.reception_channel')" :options="$channels" />
                <x-ui.form.select name="vessel_id" :label="__('movements::eez.fields.vessel')" :options="$vessels" />
                <x-ui.form.select name="reason" :label="__('movements::eez.fields.reason')" :options="$reasons" />
            </x-ui.card>

            <x-ui.card :title="__('movements::eez.identity_section')">
                <x-ui.form.input name="declared_name" :label="__('movements::eez.fields.declared_name')" />
                <x-ui.form.input name="declared_imo" :label="__('movements::eez.fields.declared_imo')" />
                <x-ui.form.input name="declared_mmsi" :label="__('movements::eez.fields.declared_mmsi')" />
                <x-ui.form.input name="declared_ircs" :label="__('movements::eez.fields.declared_ircs')" />
                <x-ui.form.input name="declared_flag" :label="__('movements::eez.fields.declared_flag')" />
                <x-ui.form.input name="declared_owner" :label="__('movements::eez.fields.declared_owner')" />
            </x-ui.card>

            <x-ui.card :title="__('movements::eez.position_section')">
                <x-ui.form.input name="crossed_at" type="datetime-local" :label="__('movements::eez.fields.crossed_at')" />
                <x-ui.form.input name="latitude" type="number" step="0.000001" :label="__('movements::eez.fields.latitude')" />
                <x-ui.form.input name="longitude" type="number" step="0.000001" :label="__('movements::eez.fields.longitude')" />
                <x-ui.form.input name="route" :label="__('movements::eez.fields.route')" />
                <x-ui.form.input name="speed" type="number" step="0.01" :label="__('movements::eez.fields.speed')" />
                <x-ui.form.input name="destination" :label="__('movements::eez.fields.destination')" />
            </x-ui.card>

            <x-ui.card :title="__('movements::eez.fields.catches')" :subtitle="__('movements::eez.add_catch')">
                @foreach (range(0, 2) as $row)
                    <div class="row mb-3">
                        <div class="col-md-7">
                            <select name="catches[{{ $row }}][species_id]" class="form-select @error('catches.'.$row.'.species_id') is-invalid @enderror" aria-label="{{ __('movements::eez.fields.species') }}">
                                <option value="">{{ __('movements::eez.fields.species') }}</option>
                                @foreach ($species as $id => $label)
                                    <option value="{{ $id }}" @selected((string) old('catches.'.$row.'.species_id') === (string) $id)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('catches.'.$row.'.species_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5">
                            <input type="number" step="0.01" name="catches[{{ $row }}][weight_kg]" value="{{ old('catches.'.$row.'.weight_kg') }}" class="form-control @error('catches.'.$row.'.weight_kg') is-invalid @enderror" placeholder="{{ __('movements::eez.fields.weight_kg') }}" aria-label="{{ __('movements::eez.fields.weight_kg') }}">
                            @error('catches.'.$row.'.weight_kg')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </x-ui.card>

            <x-ui.form.actions :cancel="route('movements.eez.index')" />
        </form>
    </div>
</x-layouts.app>
