<?php

namespace App\Support\Documents;

final readonly class GeneratedDocument
{
    public function __construct(
        public string $name,
        public string $content,
        public string $mimeType,
    ) {}
}
