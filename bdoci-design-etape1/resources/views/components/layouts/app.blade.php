@props(['title' => null])
@inject('navigation', App\Support\Navigation\Navigation::class)
@php
    $groups = $navigation->groupsFor(auth()->user());
    // Onglet actif = groupe contenant l'élément actif (sinon le premier)
    $activeKey = collect($groups)->filter(fn ($items) => collect($items)->contains(fn ($i) => $i->isActive()))->keys()->first()
        ?? collect($groups)->keys()->first();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head', ['title' => $title])
</head>
<body>
    @include('layouts.partials.preloader')

    @include('layouts.partials.header', ['groups' => $groups, 'activeKey' => $activeKey])

    <div class="gs-shell">
        @include('layouts.partials.sidebar', ['groups' => $groups, 'activeKey' => $activeKey])

        <main class="gs-content">
            @include('layouts.partials.flash')
            {{ $slot }}
        </main>
    </div>
</body>
</html>
