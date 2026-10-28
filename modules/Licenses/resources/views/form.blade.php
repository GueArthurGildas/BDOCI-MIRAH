@php($title = $license ? __('licenses::licenses.edit_title', ['number' => $license->number]) : __('licenses::licenses.create_title'))

<x-layouts.app :title="$title">
    <x-ui.page-header :title="$title" :breadcrumbs="[__('licenses::licenses.title') => route('licenses.index')]" />

    <div class="page-body">
        <x-ui.card :title="$title">
            <form method="POST" action="{{ $license ? route('licenses.update', $license) : route('licenses.store') }}">
                @csrf
                @if ($license)
                    @method('PUT')
                @else
                    <x-ui.form.select name="vessel_id" :label="__('licenses::licenses.fields.vessel')" :options="$vessels" required />
                @endif
                <x-ui.form.input name="number" :label="__('licenses::licenses.fields.number')" :value="$license?->number" maxlength="80" required />
                <x-ui.form.select name="segment" :label="__('licenses::licenses.fields.segment')" :options="App\Enums\LicenseSegment::options()" :value="$license?->segment" />
                <x-ui.form.input name="issued_on" type="date" :label="__('licenses::licenses.fields.issued_on')" :value="$license?->issued_on?->toDateString()" />
                <x-ui.form.input name="expires_on" type="date" :label="__('licenses::licenses.fields.expires_on')" :value="$license?->expires_on->toDateString()" required />
                <div class="mb-3 row">
                    <label for="zone_ids" class="form-label col-sm-3 col-form-label">{{ __('licenses::licenses.fields.zones') }}</label>
                    <div class="col-sm-9">
                        <select id="zone_ids" name="zone_ids[]" multiple size="5" class="form-select">
                            @foreach ($zones as $id => $label)
                                <option value="{{ $id }}" @selected(in_array($id, old('zone_ids', $license?->zones->pluck('id')->all() ?? [])))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="gear_ids" class="form-label col-sm-3 col-form-label">{{ __('licenses::licenses.fields.gears') }}</label>
                    <div class="col-sm-9">
                        <select id="gear_ids" name="gear_ids[]" multiple size="5" class="form-select">
                            @foreach ($gears as $id => $label)
                                <option value="{{ $id }}" @selected(in_array($id, old('gear_ids', $license?->gears->pluck('id')->all() ?? [])))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="species_ids" class="form-label col-sm-3 col-form-label">{{ __('licenses::licenses.fields.species') }}</label>
                    <div class="col-sm-9">
                        <select id="species_ids" name="species_ids[]" multiple size="5" class="form-select">
                            @foreach ($species as $id => $label)
                                <option value="{{ $id }}" @selected(in_array($id, old('species_ids', $license?->species->pluck('id')->all() ?? [])))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-ui.form.textarea name="conditions" :label="__('licenses::licenses.fields.conditions')" :value="$license?->conditions->pluck('body')->join(\"\n\")" rows="4" />
                <x-ui.form.actions :cancel="$license ? route('licenses.show', $license) : route('licenses.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
