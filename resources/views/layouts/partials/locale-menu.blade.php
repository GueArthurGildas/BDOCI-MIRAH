@inject('locales', App\Support\Localization\Locales::class)

<li class="header-notification">
    <div class="dropdown-primary dropdown">
        <div class="dropdown-toggle" data-bs-toggle="dropdown" title="{{ __('ui.change_language') }}">
            <i class="feather icon-globe"></i>
            <span>{{ $locales->nativeName($locales->current()) }}</span>
        </div>
        <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
            @foreach ($locales->all() as $locale)
                <li>
                    <a href="{{ route('locale.update', $locale) }}" @class(['f-w-600' => $locale === $locales->current()])>
                        {{ $locales->nativeName($locale) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</li>
