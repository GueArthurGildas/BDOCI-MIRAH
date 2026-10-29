@props(['name', 'label', 'options', 'value' => null])

<select name="{{ $name }}" class="form-select form-select-sm gs-toolbar-select" aria-label="{{ $label }}" data-auto-submit>
    <option value="">{{ $label }} : {{ __('ui.all') }}</option>
    @foreach ($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>{{ $optionLabel }}</option>
    @endforeach
</select>
