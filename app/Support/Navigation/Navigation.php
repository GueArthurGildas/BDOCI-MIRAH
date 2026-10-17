<?php

namespace App\Support\Navigation;

use Illuminate\Support\Collection;

final class Navigation
{
    /** @var array<int, NavigationItem> */
    private array $items = [];

    public function add(string $label, string $route, string $icon, int $order = 100): self
    {
        $this->items[] = new NavigationItem($label, $route, $icon, $order);

        return $this;
    }

    /** @return Collection<int, NavigationItem> */
    public function items(): Collection
    {
        return collect($this->items)->sortBy('order')->values();
    }
}
