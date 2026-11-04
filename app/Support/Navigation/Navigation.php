<?php

namespace App\Support\Navigation;

use Closure;
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
        ?Closure $badge = null,
        ?NavigationFolder $folder = null,
        ?string $preview = null,
    ): self {
        $this->items[] = new NavigationItem($label, $route, $icon, $folder?->group() ?? $group, $order, $permission, $badge, $folder, $preview);

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

    /** @return Collection<string, Collection<int, NavigationNode>> */
    public function treeFor(?Authorizable $user): Collection
    {
        $visibleItems = collect($this->items)->filter(fn (NavigationItem $item) => $item->isVisibleTo($user));

        return collect(NavigationGroup::cases())
            ->mapWithKeys(fn (NavigationGroup $group) => [
                $group->label() => $this->nodes($visibleItems->where('group', $group), null),
            ])
            ->filter(fn (Collection $nodes) => $nodes->isNotEmpty());
    }

    public function activeItemFor(?Authorizable $user): ?NavigationItem
    {
        return $this->groupsFor($user)->flatten(1)
            ->filter(fn (NavigationItem $item) => $item->matchScore() > 0)
            ->sortByDesc(fn (NavigationItem $item) => $item->matchScore())
            ->first();
    }

    /**
     * @param  Collection<int, NavigationItem>  $items
     * @return Collection<int, NavigationNode>
     */
    private function nodes(Collection $items, ?NavigationFolder $parent): Collection
    {
        $leaves = $items
            ->filter(fn (NavigationItem $item) => $item->folder === $parent)
            ->map(fn (NavigationItem $item) => new NavigationNode(item: $item, order: $item->order));

        $folders = collect(NavigationFolder::cases())
            ->filter(fn (NavigationFolder $folder) => $folder->parent() === $parent)
            ->map(fn (NavigationFolder $folder) => new NavigationNode(folder: $folder, children: $this->nodes($items, $folder), order: $folder->order()))
            ->filter(fn (NavigationNode $node) => $node->children->isNotEmpty());

        return $leaves->concat($folders)->sortBy('order')->values();
    }
}
