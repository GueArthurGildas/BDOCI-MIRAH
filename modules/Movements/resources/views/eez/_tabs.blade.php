<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a href="{{ route('movements.eez.index') }}" @class(['nav-link', 'active' => request()->routeIs('movements.eez.index', 'movements.eez.show', 'movements.eez.create')])>{{ __('movements::eez.tabs_declarations') }}</a></li>
    <li class="nav-item"><a href="{{ route('movements.eez.alerts') }}" @class(['nav-link', 'active' => request()->routeIs('movements.eez.alerts')])>{{ __('movements::eez.tabs_alerts') }}</a></li>
    <li class="nav-item"><a href="{{ route('movements.eez.missed.index') }}" @class(['nav-link', 'active' => request()->routeIs('movements.eez.missed.*')])>{{ __('movements::eez.tabs_missed') }}</a></li>
</ul>
