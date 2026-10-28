<?php

namespace App\Support\Navigation;

use Illuminate\Contracts\Auth\Access\Authorizable;

final readonly class NavigationItem
{
    public function __construct(
        public string $label,
        public string $route,
        public string $icon,
        public NavigationGroup $group,
        public int $order = 100,
        public ?string $permission = null,
    ) {}

    public function url(): string
    {
        return route($this->route);
    }

    public const EXACT_MATCH = PHP_INT_MAX;

    public function matchScore(): int
    {
        if (request()->routeIs($this->route)) {
            return self::EXACT_MATCH;
        }

        $routeBase = str($this->route)->chopEnd('.index')->toString();

        return request()->routeIs("{$routeBase}.*") ? strlen($routeBase) : 0;
    }

    public function isVisibleTo(?Authorizable $user): bool
    {
        return $this->permission === null || (bool) $user?->can($this->permission);
    }
}
