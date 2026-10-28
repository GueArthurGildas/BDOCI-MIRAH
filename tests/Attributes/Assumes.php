<?php

namespace Tests\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Assumes
{
    /** @var array<int, string> */
    public readonly array $ids;

    public function __construct(string $id, string ...$more)
    {
        $this->ids = [$id, ...$more];
    }
}
