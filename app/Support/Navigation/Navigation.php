<?php

namespace App\Support\Navigation;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Collection;

final class Navigation
{
    /** @var array<int, NavigationItem> */
    private array $items = [];

    public function add(
        string $label,
        string $route,
        string $icon,
        NavigationGroup $group,
        int $order = 100,
        ?string $permission = null,
    ): self {
        $this->items[] = new NavigationItem($label, $route, $icon, $group, $order, $permission);

        return $this;
    }

    /** @return Collection<string, Collection<int, NavigationItem>> */
    public function groupsFor(?Authorizable $user): Collection
    {
        $visibleItems = collect($this->items)
            ->filter(fn (NavigationItem $item) => $item->isVisibleTo($user))
            ->sortBy('order');

        return collect(NavigationGroup::cases())
            ->mapWithKeys(fn (NavigationGroup $group) => [
                $group->label() => $visibleItems->where('group', $group)->values(),
            ])
            ->filter(fn (Collection $items) => $items->isNotEmpty());
    }

    public function activeItemFor(?Authorizable $user): ?NavigationItem
    {
        return $this->groupsFor($user)->flatten(1)
            ->filter(fn (NavigationItem $item) => $item->matchScore() > 0)
            ->sortByDesc(fn (NavigationItem $item) => $item->matchScore())
            ->first();
    }
}
