<x-layouts.app :title="__('missions::missions.schedule')">
    <x-ui.page-header :title="__('missions::missions.schedule')" :subtitle="__('missions::missions.schedule_subtitle')" :breadcrumbs="[__('missions::missions.title') => route('missions.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('missions::missions.schedule')">
            <form method="POST" action="{{ route('missions.store') }}">
                @csrf
                <x-ui.form.select name="nature" :label="__('missions::missions.fields.nature')" :options="App\Enums\MissionNature::options()" :value="App\Enums\MissionNature::Internal" required />
                <x-ui.form.select name="type" :label="__('missions::missions.fields.type')" :options="App\Enums\MissionType::options()" required />
                <x-ui.form.input name="origin" :label="__('missions::missions.fields.origin')" />
                <x-ui.form.textarea name="objectives" :label="__('missions::missions.fields.objectives')" rows="3" />
                <x-ui.form.input name="planned_start_at" type="datetime-local" :label="__('missions::missions.fields.planned_start_at')" required />
                <x-ui.form.input name="planned_end_at" type="datetime-local" :label="__('missions::missions.fields.planned_end_at')" />
                <div class="mb-3 row">
                    <label for="zone_ids" class="form-label col-sm-3 col-form-label">{{ __('missions::missions.fields.zone_ids') }}</label>
                    <div class="col-sm-9">
                        <select id="zone_ids" name="zone_ids[]" multiple size="5" class="form-select">
                            @foreach ($zones as $id => $name)
                                <option value="{{ $id }}" @selected(in_array($id, old('zone_ids', [])))>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-ui.form.textarea name="points_gps" :label="__('missions::missions.fields.points_gps')" rows="3" :placeholder="__('missions::missions.points_gps_hint')" />
                <div class="mb-3 row">
                    <label for="agent_ids" class="form-label col-sm-3 col-form-label">{{ __('missions::missions.fields.agent_ids') }}</label>
                    <div class="col-sm-9">
                        <select id="agent_ids" name="agent_ids[]" multiple size="5" class="form-select">
                            @foreach ($agents as $id => $name)
                                <option value="{{ $id }}" @selected(in_array($id, old('agent_ids', [])))>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-ui.form.select name="lead_agent_id" :label="__('missions::missions.fields.lead_agent_id')" :options="$agents" />
                <div class="mb-3 row">
                    <label for="asset_ids" class="form-label col-sm-3 col-form-label">{{ __('missions::missions.fields.asset_ids') }}</label>
                    <div class="col-sm-9">
                        <select id="asset_ids" name="asset_ids[]" multiple size="5" class="form-select">
                            @foreach ($assets as $id => $label)
                                <option value="{{ $id }}" @selected(in_array($id, old('asset_ids', [])))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="witness_report_id" value="{{ $prefill['witness_report_id'] }}">
                <input type="hidden" name="operation_id" value="{{ $prefill['operation_id'] }}">
                <x-ui.form.actions :cancel="route('missions.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
