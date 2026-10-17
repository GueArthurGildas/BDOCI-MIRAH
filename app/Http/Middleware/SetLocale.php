<?php

namespace App\Http\Middleware;

use App\Support\Localization\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(private readonly Locales $locales) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        Number::useLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        return collect([$request->cookie(Locales::COOKIE), ...$request->getLanguages()])
            ->filter()
            ->map(fn (string $language) => Str::before($language, '_'))
            ->first($this->locales->isSupported(...))
            ?? config('app.locale');
    }
}
