@props(['title' => null])
@inject('navigation', App\Support\Navigation\Navigation::class)
@php
    $groups = $navigation->groupsFor(auth()->user());
    $activeGroup = $navigation->activeItemFor(auth()->user())?->group->label() ?? $groups->keys()->first();
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
        @include('layouts.partials.sidebar', ['groups' => $groups, 'activeGroup' => $activeGroup])

        <main class="gs-content">
            @include('layouts.partials.health-banner')
            @include('layouts.partials.flash')
            {{ $slot }}
        </main>
    </div>
</body>
</html>
