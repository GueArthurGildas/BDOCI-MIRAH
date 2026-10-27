{{-- Menu contextuel de gauche : éléments du groupe actif, pastilles + compteurs orange --}}
<aside class="gs-sidebar">
    <div class="gs-user">
        <img src="{{ Vite::asset('resources/images/mirah-bgless.png') }}" alt="{{ config('app.name') }}">
        <span title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
    </div>

    <ul class="gs-menu">
        @foreach ($groups[$activeKey] ?? [] as $item)
            <li @class(['active' => $item->isActive()])>
                <a href="{{ $item->url() }}">
                    <span class="gs-ico"><i class="feather {{ $item->icon }}"></i></span>
                    <span class="gs-label">{{ __($item->label) }}</span>
                    @if (($item->badge ?? null) !== null)
                        <span class="gs-badge">{{ $item->badge }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>

    <div class="gs-guide">
        <strong>{{ config('app.name') }}</strong>
        <small>{{ __('ui.guide_in_progress') }}</small>
        <a href="{{ Route::has('guide') ? route('guide') : '#!' }}">+ {{ __('ui.user_guide') }}</a>
    </div>
</aside>
