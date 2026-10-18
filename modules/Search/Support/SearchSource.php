<?php

namespace Modules\Search\Support;

use Closure;
use Illuminate\Support\Collection;

final readonly class SearchSource
{
    /** @param Closure(string): Collection<int, SearchResult> $search */
    public function __construct(
        public string $key,
        public string $icon,
        public string $permission,
        private Closure $search,
    ) {}

    /** @return Collection<int, SearchResult> */
    public function search(string $term): Collection
    {
        return ($this->search)($term);
    }
}
