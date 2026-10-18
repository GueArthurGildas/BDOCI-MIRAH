@props(['name', 'label', 'value' => null, 'rows' => 4])

<div class="mb-3 row">
    <label for="{{ $name }}" class="form-label col-sm-3 col-form-label">{{ $label }}</label>
    <div class="col-sm-9">
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
