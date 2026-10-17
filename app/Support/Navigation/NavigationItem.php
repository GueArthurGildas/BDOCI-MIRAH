<?php

namespace App\Support\Navigation;

final readonly class NavigationItem
{
    public function __construct(
        public string $label,
        public string $route,
        public string $icon,
        public int $order = 100,
    ) {}

    public function url(): string
    {
        return route($this->route);
    }

    public function isActive(): bool
    {
        return request()->routeIs($this->route, "{$this->route}.*");
    }
}
