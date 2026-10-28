<x-layouts.app :title="__('witnesses::witnesses.record')">
    <x-ui.page-header :title="__('witnesses::witnesses.record')" :subtitle="__('witnesses::witnesses.record_subtitle')" :breadcrumbs="[__('witnesses::witnesses.title') => route('witnesses.index')]" />

    <div class="page-body">
        <form method="POST" action="{{ route('witnesses.store') }}">
            @csrf
            <x-ui.card :title="__('witnesses::witnesses.sections.report')">
                <x-ui.form.input name="received_at" type="datetime-local" :label="__('witnesses::witnesses.fields.received_at')" />
                <x-ui.form.select name="channel" :label="__('witnesses::witnesses.fields.channel')" :options="$channels" required />
                <x-ui.form.input name="latitude" type="number" step="0.000001" :label="__('witnesses::witnesses.fields.latitude')" />
                <x-ui.form.input name="longitude" type="number" step="0.000001" :label="__('witnesses::witnesses.fields.longitude')" />
            </x-ui.card>

            <x-ui.card :title="__('witnesses::witnesses.sections.witness')">
                <x-ui.form.select name="witness_id" :label="__('witnesses::witnesses.fields.witness_id')" :options="$witnesses" />
                <x-ui.form.input name="witness_name" :label="__('witnesses::witnesses.fields.witness_name')" />
                <x-ui.form.select name="witness_category" :label="__('witnesses::witnesses.fields.witness_category')" :options="$categories" />
                <x-ui.form.input name="witness_email" type="email" :label="__('witnesses::witnesses.fields.witness_email')" />
                <x-ui.form.input name="witness_phone" :label="__('witnesses::witnesses.fields.witness_phone')" />
            </x-ui.card>

            @foreach ([0, 1] as $index)
                <x-ui.card :title="__('witnesses::witnesses.sections.vessel', ['rank' => $index + 1])">
                    <x-ui.form.input name="vessels[{{ $index }}][name]" :label="__('witnesses::witnesses.fields.name')" :value="old('vessels.'.$index.'.name')" />
                    <x-ui.form.input name="vessels[{{ $index }}][flag]" :label="__('witnesses::witnesses.fields.flag')" :value="old('vessels.'.$index.'.flag')" />
                    <x-ui.form.input name="vessels[{{ $index }}][registration_port]" :label="__('witnesses::witnesses.fields.registration_port')" :value="old('vessels.'.$index.'.registration_port')" />
                    <x-ui.form.input name="vessels[{{ $index }}][type]" :label="__('witnesses::witnesses.fields.type')" :value="old('vessels.'.$index.'.type')" />
                    <x-ui.form.input name="vessels[{{ $index }}][ircs]" :label="__('witnesses::witnesses.fields.ircs')" :value="old('vessels.'.$index.'.ircs')" />
                    <x-ui.form.input name="vessels[{{ $index }}][registration]" :label="__('witnesses::witnesses.fields.registration')" :value="old('vessels.'.$index.'.registration')" />
                    <x-ui.form.input name="vessels[{{ $index }}][other_marks]" :label="__('witnesses::witnesses.fields.other_marks')" :value="old('vessels.'.$index.'.other_marks')" />
                    <x-ui.form.input name="vessels[{{ $index }}][gear]" :label="__('witnesses::witnesses.fields.gear')" :value="old('vessels.'.$index.'.gear')" />
                    <x-ui.form.input name="vessels[{{ $index }}][route]" :label="__('witnesses::witnesses.fields.route')" :value="old('vessels.'.$index.'.route')" />
                    <x-ui.form.checkbox name="vessels[{{ $index }}][is_fishing]" :label="__('witnesses::witnesses.fields.is_fishing')" />
                    <x-ui.form.checkbox name="vessels[{{ $index }}][is_drifting]" :label="__('witnesses::witnesses.fields.is_drifting')" />
                    <x-ui.form.checkbox name="vessels[{{ $index }}][is_resupplying]" :label="__('witnesses::witnesses.fields.is_resupplying')" />
                    <x-ui.form.checkbox name="vessels[{{ $index }}][is_transshipping]" :label="__('witnesses::witnesses.fields.is_transshipping')" />
                    <x-ui.form.textarea name="vessels[{{ $index }}][description]" :label="__('witnesses::witnesses.fields.description')" :value="old('vessels.'.$index.'.description')" rows="3" />
                </x-ui.card>
            @endforeach

            <x-ui.form.actions :cancel="route('witnesses.index')" />
        </form>
    </div>
</x-layouts.app>
