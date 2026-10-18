<nav class="navbar header-navbar pcoded-header">
    <div class="navbar-wrapper">
        <div class="navbar-logo">
            <a class="mobile-menu" id="mobile-collapse" href="#!">
                <i class="feather icon-menu"></i>
            </a>
            <a href="{{ url('/') }}">
                <img class="img-fluid" src="{{ Vite::asset('resources/images/logo.png') }}" alt="{{ config('app.name') }}">
            </a>
            <a class="mobile-options">
                <i class="feather icon-more-horizontal"></i>
            </a>
        </div>

        <div class="navbar-container">
            <ul class="nav-left">
                @if (Route::has('search'))
                    <li class="header-search">
                        <form method="GET" action="{{ route('search') }}" class="main-search morphsearch-search" data-header-search>
                            <div class="input-group">
                                <span class="input-group-text search-close"><i class="feather icon-x"></i></span>
                                <input type="search" name="q" class="form-control" placeholder="{{ __('ui.search') }}" aria-label="{{ __('ui.search') }}">
                                <span class="input-group-text search-btn"><i class="feather icon-search"></i></span>
                            </div>
                        </form>
                    </li>
                @endif
                <li>
                    <a href="#!" class="waves-effect waves-light" data-toggle-fullscreen>
                        <i class="full-screen feather icon-maximize"></i>
                    </a>
                </li>
            </ul>
            <ul class="nav-right">
                @include('layouts.partials.locale-menu')
                <li class="user-profile header-notification">
                    <div class="dropdown-primary dropdown">
                        <div class="dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="{{ Vite::asset('resources/images/avatar-blank.jpg') }}" class="img-radius" alt="{{ auth()->user()->name }}">
                            <span>{{ auth()->user()->name }}</span>
                            <i class="feather icon-chevron-down"></i>
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
        </div>
    </div>
</nav>
