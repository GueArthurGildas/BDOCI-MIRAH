@props(['name', 'label', 'checked' => false])

<div class="mb-3 row">
    <div class="col-sm-9 offset-sm-3">
        <div class="checkbox-fade fade-in-primary">
            <label class="form-label">
                <input type="hidden" name="{{ $name }}" value="0">
                <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))>
                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                <span>{{ $label }}</span>
            </label>
        </div>
        @error($name)
            <div class="text-danger f-12">{{ $message }}</div>
        @enderror
    </div>
</div>
