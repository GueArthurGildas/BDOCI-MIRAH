<x-layouts.app :title="__('communications::notes.new')">
    <x-ui.page-header :title="__('communications::notes.new')" :breadcrumbs="[__('communications::notes.title') => route('communications.notes.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('communications::notes.new')">
            <form method="POST" action="{{ route('communications.notes.store') }}">
                @csrf
                @include('communications::partials.choice-buttons', ['name' => 'type', 'label' => __('communications::fields.type'), 'options' => $types, 'required' => true])
                <x-ui.form.select name="vessel_id" :label="__('communications::fields.vessel_id')" :options="$vessels" :value="$selectedVessel" />
                @include('communications::partials.choice-buttons', ['name' => 'urgency', 'label' => __('communications::fields.urgency'), 'options' => $urgencies, 'selected' => 'normal', 'required' => true])

                <div class="mb-3 row">
                    <label for="recipient" class="form-label col-sm-3 col-form-label">{{ __('communications::fields.recipient') }}</label>
                    <div class="col-sm-9">
                        <select id="recipient" name="recipient" @class(['form-select', 'is-invalid' => $errors->has('recipient')]) required>
                            <option value="">{{ __('ui.select_placeholder') }}</option>
                            @foreach ($recipients as $group => $options)
                                <optgroup label="{{ $group }}">
                                    @foreach ($options as $value => $label)
                                        <option value="{{ $value }}" @selected(old('recipient') === $value)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('recipient')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <x-ui.form.input name="due_date" type="date" :label="__('communications::fields.due_date')" />
                <x-ui.form.textarea name="free_text" :label="__('communications::fields.free_text')" />
                @include('communications::partials.choice-buttons', ['name' => 'relay', 'label' => __('communications::fields.relay'), 'options' => ['' => __('communications::notes.no_relay'), ...App\Enums\NoteRelay::options()], 'selected' => ''])

                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather icon-save"></i> {{ __('communications::notes.leave') }}
                        </button>
                        @can(App\Support\Authorization\Permission::CommunicationsTriggerAiAgent->value)
                            <button type="submit" formaction="{{ route('communications.notes.ai') }}" class="btn btn-outline-primary m-l-10">
                                <i class="feather icon-cpu"></i> {{ __('communications::notes.ask_ai_agent') }}
                            </button>
                        @endcan
                        <a href="{{ route('communications.notes.index') }}" class="btn btn-default m-l-10">{{ __('ui.cancel') }}</a>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
