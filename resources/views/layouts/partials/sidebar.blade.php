<aside class="gs-sidebar">
    <div class="gs-user">
        <img src="{{ Vite::asset('resources/images/mirah-bgless.png') }}" alt="{{ config('app.name') }}">
        <span title="{{ auth()->user()->fullName() }}">{{ auth()->user()->fullName() }}</span>
    </div>

    <ul class="gs-menu">
        @foreach ($tree[$activeGroup] ?? [] as $node)
            @include('layouts.partials.menu-node', ['node' => $node, 'activeItem' => $activeItem])
        @endforeach
    </ul>

    <div class="gs-guide">
        <strong>{{ config('app.name') }}</strong>
        <small>{{ __('ui.guide_hint') }}</small>

        --test gildas ici depuis windows----
        <button type="button" class="btn btn-primary btn-sm" data-gs-guide><i class="feather icon-compass"></i> {{ __('ui.user_guide') }}</button>
    </div>
</aside>
