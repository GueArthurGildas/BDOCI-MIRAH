<?php

namespace Modules\Search\Support;

final readonly class SearchResult
{
    public function __construct(
        public string $title,
        public ?string $subtitle,
        public ?string $url,
    ) {}
}
