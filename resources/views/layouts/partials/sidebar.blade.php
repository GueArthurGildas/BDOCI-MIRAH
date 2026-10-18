@inject('navigation', App\Support\Navigation\Navigation::class)

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        @foreach ($navigation->groupsFor(auth()->user()) as $groupLabel => $items)
            <div class="pcoded-navigatio-lavel">{{ __($groupLabel) }}</div>
            <ul class="pcoded-item pcoded-left-item">
                @foreach ($items as $item)
                    <li @class(['active' => $item->isActive()])>
                        <a href="{{ $item->url() }}">
                            <span class="pcoded-micon"><i class="feather {{ $item->icon }}"></i></span>
                            <span class="pcoded-mtext">{{ __($item->label) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
</nav>
