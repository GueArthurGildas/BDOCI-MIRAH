@props(['route', 'icon'])

<li @class(['active' => request()->routeIs($route, "$route.*")])>
    <a href="{{ route($route) }}">
        <span class="pcoded-micon"><i class="feather {{ $icon }}"></i></span>
        <span class="pcoded-mtext">{{ $slot }}</span>
    </a>
</li>
