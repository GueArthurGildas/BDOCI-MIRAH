{{-- Barre orange supérieure : un onglet par groupe de navigation (Dashboard, Administration, ...) --}}
<div class="gs-topbar" role="banner">
    <ul class="gs-tabs">
        @foreach ($groups as $groupLabel => $items)
            @php($first = collect($items)->first())
            <li @class(['gs-tab', 'active' => $groupLabel === $activeKey])>
                <a href="{{ $first?->url() ?? '#' }}">
                    <i class="feather {{ $first?->icon ?? 'icon-grid' }}"></i>
                    <span>{{ __($groupLabel) }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="gs-topbar-right">
        {{-- Balise d'alerte : point rouge qui s'allume / s'éteint + compteur (démo : alertes simulées) --}}
        <a href="#!" class="gs-beacon" data-alert-beacon title="{{ __('ui.alerts') }}">
            <i class="feather icon-bell"></i>
            <span class="gs-beacon-dot"></span>
            <span class="gs-beacon-count" data-alert-count>0</span>
        </a>
        @if (Route::has('search'))
            <a href="{{ route('search') }}" title="{{ __('ui.search') }}"><i class="feather icon-search"></i></a>
        @endif
        <a href="#!" data-toggle-fullscreen title="{{ __('ui.fullscreen') }}"><i class="full-screen feather icon-maximize"></i></a>
        @include('layouts.partials.locale-menu')
        <div class="dropdown">
            <div class="dropdown-toggle" data-bs-toggle="dropdown">
                <img src="{{ Vite::asset('resources/images/avatar-blank.jpg') }}" class="img-radius" alt="{{ auth()->user()->name }}">
                <span class="gs-user-name">{{ auth()->user()->name }}</span>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="feather icon-log-out"></i> {{ __('ui.log_out') }}
                        </a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
