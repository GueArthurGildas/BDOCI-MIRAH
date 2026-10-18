@props(['name', 'label', 'options', 'value' => null, 'placeholder' => null])

@php($selected = (string) old($name, $value instanceof BackedEnum ? $value->value : $value))

<div class="mb-3 row">
    <label for="{{ $name }}" class="form-label col-sm-3 col-form-label">{{ $label }}</label>
    <div class="col-sm-9">
        <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}>
            <option value="">{{ $placeholder ?? __('ui.select_placeholder') }}</option>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $optionValue === $selected)>{{ $optionLabel }}</option>
            @endforeach
        </select>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
