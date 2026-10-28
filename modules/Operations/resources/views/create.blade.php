<x-layouts.app :title="__('operations::operations.create_title')">
    <x-ui.page-header :title="__('operations::operations.create_title')" :breadcrumbs="[__('operations::operations.title') => route('operations.index')]" />

    <div class="page-body">
        <form method="POST" action="{{ route('operations.store') }}">
            @csrf

            <x-ui.card :title="__('operations::operations.create_title')" :subtitle="__('operations::operations.code_hint')">
                <x-ui.form.select name="category_id" :label="__('operations::operations.fields.category')" :options="$categories" required />
                <x-ui.form.input name="name" :label="__('operations::operations.fields.name')" maxlength="120" required />
                <x-ui.form.input name="started_at" type="datetime-local" :label="__('operations::operations.fields.started_at')" :value="now()->format('Y-m-d\TH:i')" />
                <x-ui.form.input name="origin" :label="__('operations::operations.fields.origin')" maxlength="60" />
                <div class="mb-3 row">
                    <label for="vessel_ids" class="form-label col-sm-3 col-form-label">{{ __('operations::operations.fields.vessels') }}</label>
                    <div class="col-sm-9">
                        <select id="vessel_ids" name="vessel_ids[]" multiple size="6" @class(['form-select', 'is-invalid' => $errors->has('vessel_ids')])>
                            @foreach ($vessels as $id => $label)
                                <option value="{{ $id }}" @selected(in_array($id, old('vessel_ids', [])))>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('vessel_ids')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <x-ui.form.textarea name="summary" :label="__('operations::operations.fields.summary')" rows="3" />
            </x-ui.card>

            <x-ui.card :title="__('operations::operations.context_title')">
                <x-ui.form.textarea name="first_event" :label="__('operations::operations.fields.first_event')" rows="3" />
                <x-ui.form.input name="temperature" type="number" step="0.1" :label="__('operations::operations.fields.temperature')" />
                <x-ui.form.input name="pressure" type="number" step="0.1" :label="__('operations::operations.fields.pressure')" />
                <x-ui.form.select name="visibility" :label="__('operations::operations.fields.visibility')" :options="$visibilities" />
                <x-ui.form.select name="cloud_cover" :label="__('operations::operations.fields.cloud_cover')" :options="$cloudCovers" />
                <x-ui.form.input name="wind_beaufort" type="number" min="0" max="12" :label="__('operations::operations.fields.wind_beaufort')" />
                <x-ui.form.select name="sea_state" :label="__('operations::operations.fields.sea_state')" :options="$seaStates" />
                <x-ui.form.actions :cancel="route('operations.index')" />
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
