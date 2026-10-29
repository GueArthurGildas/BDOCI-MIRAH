@props(['name', 'label', 'type' => 'text', 'value' => null])

@if ($type === 'search')
    <input type="search" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $attributes->get('placeholder', $label) }}" aria-label="{{ $label }}" {{ $attributes->except(['class', 'placeholder'])->class('form-control form-control-sm gs-toolbar-search') }}>
@else
    <label class="gs-toolbar-field">
        {{ $label }}
        <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" data-auto-submit {{ $attributes->except('class')->class('form-control form-control-sm') }}>
    </label>
@endif
