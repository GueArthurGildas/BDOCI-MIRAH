@props(['label', 'value', 'icon', 'color' => 'warning', 'footer' => null])

<div class="card gs-stat is-{{ $color }}">
    <div class="gs-stat-icon"><i class="feather {{ $icon }}"></i></div>
    <div>
        <p class="gs-stat-value">{{ $value }}</p>
        <p class="gs-stat-label">{{ $label }}</p>
        @if ($footer)
            <small class="text-muted">{{ $footer }}</small>
        @endif
    </div>
</div>
