@props(['name', 'form', 'value' => null, 'options' => null, 'type' => 'search'])

<th>
    @if ($options === null)
        <input type="{{ $type }}" name="{{ $name }}" form="{{ $form }}" value="{{ $value }}" placeholder="{{ __('ui.filter_placeholder') }}" aria-label="{{ __('ui.filter') }}" data-auto-submit {{ $attributes->class('form-control form-control-sm') }}>
    @else
        <select name="{{ $name }}" form="{{ $form }}" aria-label="{{ __('ui.filter') }}" data-auto-submit {{ $attributes->class('form-select form-select-sm') }}>
            <option value="">{{ __('ui.all') }}</option>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @endif
</th>
