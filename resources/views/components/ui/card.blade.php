@props(['title', 'subtitle' => null, 'flush' => false, 'actions' => null])

<div {{ $attributes->class('card') }}>
    <div class="card-header">
        <h5>{{ $title }}</h5>
        @if ($subtitle)
            <span class="text-muted">{{ $subtitle }}</span>
        @endif
        <div class="card-header-right">
            @if ($actions)
                {{ $actions }}
            @else
                <ul class="list-unstyled card-option">
                    <li><i class="feather icon-maximize full-card"></i></li>
                    <li><i class="feather icon-minus minimize-card"></i></li>
                </ul>
            @endif
        </div>
    </div>
    <div @class(['card-body', 'p-0' => $flush])>
        {{ $slot }}
    </div>
</div>
