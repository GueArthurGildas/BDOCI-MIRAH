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

    public function isActive(): bool
    {
        $routeBase = str($this->route)->chopEnd('.index')->toString();

        return request()->routeIs($this->route, "{$routeBase}.*");
    }

    public function isVisibleTo(?Authorizable $user): bool
    {
        return $this->permission === null || (bool) $user?->can($this->permission);
    }
}
