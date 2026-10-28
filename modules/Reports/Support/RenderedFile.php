<?php

namespace Modules\Reports\Support;

final readonly class RenderedFile
{
    public function __construct(public string $content, public string $mimeType, public string $extension) {}
}
