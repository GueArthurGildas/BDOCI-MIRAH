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

    @if (Route::has('guide'))
        <div class="gs-guide">
            <strong>{{ config('app.name') }}</strong>
            <small>{{ __('ui.guide_in_progress') }}</small>
            <a href="{{ route('guide') }}" class="btn btn-primary btn-sm">{{ __('ui.user_guide') }}</a>
        </div>
    @endif
</aside>
