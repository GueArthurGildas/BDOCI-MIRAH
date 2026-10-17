@inject('locales', App\Support\Localization\Locales::class)

<p class="text-center m-t-20">
    @foreach ($locales->all() as $locale)
        <a href="{{ route('locale.update', $locale) }}" @class(['text-white mx-2', 'f-w-600' => $locale === $locales->current()])>
            {{ $locales->nativeName($locale) }}
        </a>
    @endforeach
</p>
