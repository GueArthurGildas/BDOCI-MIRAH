@props(['label', 'value', 'icon', 'color' => 'warning', 'footer' => null])

<div class="card">
    <div class="gs-stat">
        <div class="gs-stat-icon is-{{ $color }}"><i class="feather {{ $icon }}"></i></div>
        <div>
            <p class="gs-stat-value">{{ $value }}</p>
            <p class="gs-stat-label">{{ $label }}</p>
            @if ($footer)
                <small class="text-muted">{{ $footer }}</small>
            @endif
        </div>
    </div>
</div>
