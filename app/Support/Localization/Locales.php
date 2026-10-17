<?php

namespace App\Support\Localization;

final readonly class Locales
{
    public const COOKIE = 'locale';

    public const COOKIE_LIFETIME_IN_MINUTES = 60 * 24 * 365;

    /** @param array<int, string> $supported */
    public function __construct(private array $supported) {}

    /** @return array<int, string> */
    public function all(): array
    {
        return $this->supported;
    }

    public function isSupported(?string $locale): bool
    {
        return in_array($locale, $this->supported, true);
    }

    public function current(): string
    {
        return app()->getLocale();
    }

    public function nativeName(string $locale): string
    {
        return trans('ui.language', locale: $locale);
    }
}
