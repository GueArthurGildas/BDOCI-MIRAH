@if ($node->isFolder())
    <li @class(['gs-folder', 'is-open' => $node->contains($activeItem)])>
        <button type="button" class="gs-folder-row" data-gs-folder aria-expanded="{{ $node->contains($activeItem) ? 'true' : 'false' }}">
            <span class="gs-icon"><i class="feather {{ $node->icon() }}"></i></span>
            <span class="gs-label">{{ __($node->label()) }}</span>
            <span class="gs-folder-caret"><i class="feather icon-chevron-down"></i></span>
        </button>
        <ul class="gs-submenu">
            @foreach ($node->children as $child)
                @include('layouts.partials.menu-node', ['node' => $child, 'activeItem' => $activeItem])
            @endforeach
        </ul>
    </li>
@else
    <li @class(['active' => $node->item === $activeItem])>
        <a href="{{ $node->item->url() }}">
            <span class="gs-icon"><i class="feather {{ $node->icon() }}"></i></span>
            <span class="gs-label">{{ __($node->label()) }}</span>
            @if ($count = $node->badgeCount())
                <span class="gs-badge">{{ $count }}</span>
            @endif
        </a>
    </li>
@endif
