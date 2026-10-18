@props(['name', 'label', 'type' => 'text', 'value' => null])

<div class="mb-3 row">
    <label for="{{ $name }}" class="form-label col-sm-3 col-form-label">{{ $label }}</label>
    <div class="col-sm-9">
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
