@props(['name', 'label', 'type' => 'text', 'value' => null])

<x-ui.filter-field :label="$label" :class="$attributes->get('class', 'col-md')">
    <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" {{ $attributes->except('class')->class('form-control form-control-sm') }}>
</x-ui.filter-field>
