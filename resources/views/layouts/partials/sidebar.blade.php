@inject('navigation', App\Support\Navigation\Navigation::class)

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">{{ __('ui.navigation') }}</div>
        <ul class="pcoded-item pcoded-left-item">
            @foreach ($navigation->items() as $item)
                <li @class(['active' => $item->isActive()])>
                    <a href="{{ $item->url() }}">
                        <span class="pcoded-micon"><i class="feather {{ $item->icon }}"></i></span>
                        <span class="pcoded-mtext">{{ __($item->label) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
