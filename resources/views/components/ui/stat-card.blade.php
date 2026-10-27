@props(['label', 'value', 'icon', 'color' => 'warning', 'footer' => null, 'compact' => false])

<div @class(['card', 'gs-stat', 'is-'.$color, 'is-compact' => $compact])>
    <div class="gs-stat-icon"><i class="feather {{ $icon }}"></i></div>
    <div>
        <p class="gs-stat-value">{{ $value }}</p>
        <p class="gs-stat-label">{{ $label }}</p>
        @if ($footer)
            <small class="text-muted">{{ $footer }}</small>
        @endif
    </div>
</div>
