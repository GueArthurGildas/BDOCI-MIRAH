<x-layouts.app :title="__('infractions::infractions.open_case')">
    <x-ui.page-header :title="__('infractions::infractions.open_case')" :subtitle="__('infractions::infractions.open_subtitle')" :breadcrumbs="[__('infractions::infractions.title') => route('infractions.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('infractions::infractions.open_case')">
            <form method="POST" action="{{ route('infractions.store') }}">
                @csrf
                <x-ui.form.select name="vessel_id" :label="__('infractions::infractions.fields.vessel')" :options="$vessels" :value="$prefill['vessel_id']" required />
                <x-ui.form.textarea name="facts" :label="__('infractions::infractions.fields.facts')" :value="$prefill['facts']" rows="4" required />
                <x-ui.form.select name="qualification" :label="__('infractions::infractions.fields.qualification')" :options="$qualifications" :value="App\Enums\InfractionQualification::Presumed" />
                <div class="mb-3 row">
                    <label for="infraction_type_ids" class="form-label col-sm-3 col-form-label">{{ __('infractions::infractions.fields.infraction_type_ids') }}</label>
                    <div class="col-sm-9">
                        <select id="infraction_type_ids" name="infraction_type_ids[]" multiple size="6" class="form-select">
                            @foreach ($types as $id => $label)
                                <option value="{{ $id }}" @selected(in_array($id, old('infraction_type_ids', [])))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-ui.form.select name="inspection_id" :label="__('infractions::infractions.fields.inspection')" :options="$inspections" :value="$prefill['inspection_id']" />
                <x-ui.form.select name="mission_id" :label="__('infractions::infractions.fields.mission')" :options="$missions" :value="$prefill['mission_id']" />
                <x-ui.form.select name="witness_report_id" :label="__('infractions::infractions.fields.witness_report')" :options="$reports" :value="$prefill['witness_report_id']" />
                <input type="hidden" name="operation_id" value="{{ $prefill['operation_id'] }}">
                <x-ui.form.textarea name="captain_observations" :label="__('infractions::infractions.fields.captain_observations')" rows="3" />
                <x-ui.form.actions :cancel="route('infractions.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
