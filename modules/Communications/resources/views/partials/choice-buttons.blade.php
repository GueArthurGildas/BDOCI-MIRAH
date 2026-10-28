<div class="mb-3 row">
    <label class="form-label col-sm-3 col-form-label">{{ $label }}</label>
    <div class="col-sm-9">
        <div class="btn-group flex-wrap" role="group">
            @foreach ($options as $optionValue => $optionLabel)
                <input type="radio" class="btn-check" name="{{ $name }}" id="{{ $name }}_{{ $optionValue }}" value="{{ $optionValue }}" autocomplete="off" @checked((string) old($name, $selected ?? '') === (string) $optionValue) @required($required ?? false)>
                <label class="btn btn-outline-primary" for="{{ $name }}_{{ $optionValue }}">{{ $optionLabel }}</label>
            @endforeach
        </div>
        @error($name)
            <div class="text-danger f-12">{{ $message }}</div>
        @enderror
    </div>
</div>
