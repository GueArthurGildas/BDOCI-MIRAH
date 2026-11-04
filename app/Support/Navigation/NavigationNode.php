<?php

namespace App\Support\Navigation;

use Illuminate\Support\Collection;

final readonly class NavigationNode
{
    /** @param Collection<int, NavigationNode> $children */
    public function __construct(
        public ?NavigationItem $item = null,
        public ?NavigationFolder $folder = null,
        public Collection $children = new Collection,
        public int $order = 100,
    ) {}

    public function isFolder(): bool
    {
        return $this->folder !== null;
    }

    public function label(): string
    {
        return $this->folder?->label() ?? $this->item->label;
    }

    public function icon(): string
    {
        return $this->folder?->icon() ?? $this->item->icon;
    }

    public function firstUrl(): string
    {
        return $this->isFolder() ? $this->children->first()->firstUrl() : $this->item->url();
    }

    public function contains(?NavigationItem $active): bool
    {
        if ($active === null) {
            return false;
        }

        return $this->item === $active || $this->children->contains(fn (NavigationNode $child) => $child->contains($active));
    }

    public function previewUrl(): ?string
    {
        return $this->isFolder() ? null : $this->item->previewUrl();
    }

    public function badgeCount(): ?int
    {
        return $this->isFolder() ? null : $this->item->badgeCount();
    }
}
