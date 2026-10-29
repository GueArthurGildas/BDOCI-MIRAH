<aside class="gs-sidebar">
    <div class="gs-user">
        <img src="{{ Vite::asset('resources/images/mirah-bgless.png') }}" alt="{{ config('app.name') }}">
        <span title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
    </div>

    <ul class="gs-menu">
        @foreach ($groups[$activeGroup] ?? [] as $item)
            <li @class(['active' => $item === $activeItem])>
                <a href="{{ $item->url() }}">
                    <span class="gs-icon"><i class="feather {{ $item->icon }}"></i></span>
                    <span class="gs-label">{{ __($item->label) }}</span>
                    @if ($count = $item->badgeCount())
                        <span class="gs-badge">{{ $count }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>

    <div class="gs-guide">
        <strong>{{ config('app.name') }}</strong>
        <small>{{ __('ui.guide_hint') }}</small>
        <button type="button" class="btn btn-primary btn-sm" data-gs-guide><i class="feather icon-compass"></i> {{ __('ui.user_guide') }}</button>
    </div>
</aside>
