@props(['name', 'label', 'options', 'value' => null])

<x-ui.filter-field :label="$label" :class="$attributes->get('class', 'col-md')">
    <select name="{{ $name }}" class="form-select form-select-sm">
        <option value="">{{ __('ui.all') }}</option>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</x-ui.filter-field>
