@props(['label'])

<div {{ $attributes->class('col-md') }}>
    <label class="form-label f-12 text-muted">{{ $label }}</label>
    {{ $slot }}
</div>
