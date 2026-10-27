@php($isBoolean = $definition->type === App\Support\Settings\SettingType::Boolean)
@php($current = old($field, $settings->value($definition)))

<div class="mb-3 row align-items-center">
    <label for="{{ $field }}" class="form-label col-sm-5 col-form-label">{{ __('administration::settings.fields.'.$field) }}</label>
    <div class="col-sm-3">
        @if ($isBoolean)
            <input type="hidden" name="{{ $field }}" value="0">
            <div class="form-check form-switch">
                <input type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" class="form-check-input" @checked($current)>
            </div>
        @else
            <input type="number" id="{{ $field }}" name="{{ $field }}" value="{{ $current }}" min="{{ $definition->min }}" max="{{ $definition->max }}" @class(['form-control', 'is-invalid' => $errors->has($field)])>
        @endif
        @error($field)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-sm-4 text-muted f-12">
        @unless ($isBoolean)
            {{ __('administration::settings.range', ['min' => $definition->min, 'max' => $definition->max]) }} ·
        @endunless
        {{ __('administration::settings.default', ['value' => $isBoolean ? __($settings->baseline($definition) ? 'ui.yes' : 'ui.no') : $settings->baseline($definition)]) }}
        @if ($settings->isCustomized($definition))
            <span class="label label-primary m-l-5">{{ __('administration::settings.customized') }}</span>
        @endif
    </div>
</div>
