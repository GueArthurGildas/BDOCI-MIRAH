@props(['title' => null])
@inject('navigation', App\Support\Navigation\Navigation::class)
@php
    $groups = $navigation->groupsFor(auth()->user());
    $tree = $navigation->treeFor(auth()->user());
    $activeItem = $navigation->activeItemFor(auth()->user());
    $activeGroup = $activeItem?->group->label() ?? $groups->keys()->first();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-gs-no-results="{{ __('ui.no_results') }}" data-gs-select-placeholder="{{ __('ui.select_placeholder') }}" data-gs-pager-previous="{{ __('ui.pager.previous') }}" data-gs-pager-next="{{ __('ui.pager.next') }}" data-gs-pager-range="{{ __('ui.pager.range') }}">
<head>
    @include('layouts.partials.head', ['title' => $title])
</head>
<body>
    @include('layouts.partials.preloader')

    @include('layouts.partials.header', ['tree' => $tree, 'activeGroup' => $activeGroup])

    <div class="gs-shell">
        @include('layouts.partials.sidebar', ['tree' => $tree, 'activeGroup' => $activeGroup, 'activeItem' => $activeItem])

        <main class="gs-content">
            @include('layouts.partials.health-banner')
            @include('layouts.partials.flash')
            {{ $slot }}
        </main>
    </div>

    @include('layouts.partials.confirm-modal')
    @include('layouts.partials.remote-modal')
    @include('layouts.partials.popup')
    @include('layouts.partials.guide')
</body>
</html>
