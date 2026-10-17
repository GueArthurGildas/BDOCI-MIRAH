<?php

namespace Tests\Feature;

use App\Support\Localization\Locales;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_default_locale_is_used_when_browser_language_is_unsupported(): void
    {
        config(['app.locale' => 'en']);

        $this->withHeader('Accept-Language', 'de-DE,de;q=0.9')
            ->get('/login')
            ->assertSee('Sign in');
    }

    public function test_browser_language_variants_are_matched(): void
    {
        $this->withHeader('Accept-Language', 'fr-CI,fr;q=0.9')
            ->get('/login')
            ->assertSee('Connexion');
    }

    public function test_browser_language_is_used_when_supported(): void
    {
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/login')
            ->assertSee('Sign in');
    }

    public function test_chosen_locale_takes_precedence_over_browser_language(): void
    {
        $this->withHeader('Accept-Language', 'en')
            ->withCookie(Locales::COOKIE, 'fr')
            ->get('/login')
            ->assertSee('Connexion');
    }

    public function test_user_can_switch_locale(): void
    {
        $this->from('/login')
            ->get('/locale/en')
            ->assertRedirect('/login')
            ->assertCookie(Locales::COOKIE, 'en');
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->get('/locale/de')->assertNotFound();
    }
}
