<header class="gs-topbar">
    <ul class="gs-tabs">
        @foreach ($groups as $groupLabel => $items)
            @php($firstItem = $items->first())
            <li @class(['gs-tab', 'active' => $groupLabel === $activeGroup])>
                <a href="{{ $firstItem->url() }}">
                    <i class="feather {{ $firstItem->icon }}"></i>
                    <span>{{ __($groupLabel) }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <ul class="gs-tools">
        @if (Route::has('notifications.feed'))
            <li>
                <a href="{{ route('notifications.index') }}" class="gs-beacon" title="{{ __('ui.notifications') }}"
                   data-notification-beacon
                   data-feed-url="{{ route('notifications.feed') }}"
                   data-interval="{{ config('surveillance.notification_poll_seconds') * 1000 }}"
                   data-toast-seconds="{{ config('surveillance.notification_toast_seconds') }}"
                   data-max-toasts="{{ config('surveillance.notification_max_toasts') }}"
                   data-close-label="{{ __('ui.close') }}">
                    <i class="feather icon-bell"></i>
                    <span class="gs-beacon-dot"></span>
                    <span class="gs-beacon-count" data-notification-count>0</span>
                </a>
            </li>
        @endif
        @if (Route::has('search'))
            <li>
                <a href="{{ route('search') }}" title="{{ __('ui.search') }}"><i class="feather icon-search"></i></a>
            </li>
        @endif
        <li>
            <a href="#!" data-gs-guide title="{{ __('ui.user_guide') }}"><i class="feather icon-help-circle"></i></a>
        </li>
        <li>
            <a href="#!" data-toggle-fullscreen title="{{ __('ui.fullscreen') }}"><i class="full-screen feather icon-maximize"></i></a>
        </li>
        @include('layouts.partials.locale-menu')
        <li class="header-notification">
            <div class="dropdown-primary dropdown">
                <div class="dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="{{ Vite::asset('resources/images/avatar-blank.jpg') }}" class="img-radius" alt="{{ auth()->user()->name }}">
                    <span class="gs-user-name">{{ auth()->user()->name }}</span>
                </div>
                <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
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
        </li>
    </ul>
</header>
