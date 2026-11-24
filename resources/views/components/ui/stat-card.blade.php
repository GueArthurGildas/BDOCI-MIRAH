@props(['label', 'value', 'icon', 'color' => 'warning', 'footer' => null, 'compact' => false, 'actions' => null])

<div @class(['card', 'gs-stat', 'is-'.$color, 'is-compact' => $compact, 'has-actions' => $actions !== null])>
    <div class="gs-stat-icon"><i class="feather {{ $icon }}"></i></div>
    <div class="gs-stat-text">
        <p class="gs-stat-value">{{ $value }}</p>
        <p class="gs-stat-label">{{ $label }}</p>
        @if ($footer)
            <small class="text-muted">{{ $footer }}</small>
        @endif
    </div>
    @if ($actions)
        <div class="gs-stat-actions">{{ $actions }}</div>
    @endif
</div>
