@props(['title' => null])
@inject('navigation', App\Support\Navigation\Navigation::class)
@php
    $groups = $navigation->groupsFor(auth()->user());
    $activeItem = $navigation->activeItemFor(auth()->user());
    $activeGroup = $activeItem?->group->label() ?? $groups->keys()->first();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head', ['title' => $title])
</head>
<body>
    @include('layouts.partials.preloader')

    @include('layouts.partials.header', ['groups' => $groups, 'activeGroup' => $activeGroup])

    <div class="gs-shell">
        @include('layouts.partials.sidebar', ['groups' => $groups, 'activeGroup' => $activeGroup, 'activeItem' => $activeItem])

        <main class="gs-content">
            @include('layouts.partials.health-banner')
            @include('layouts.partials.flash')
            {{ $slot }}
        </main>
    </div>

    @include('layouts.partials.confirm-modal')
</body>
</html>
