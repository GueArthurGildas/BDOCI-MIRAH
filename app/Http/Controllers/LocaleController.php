<?php

namespace App\Http\Controllers;

use App\Support\Localization\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        Cookie::queue(Locales::COOKIE, $locale, Locales::COOKIE_LIFETIME_IN_MINUTES);

        return back();
    }
}
