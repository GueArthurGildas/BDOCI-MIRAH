<?php

namespace App\Support\Presentation;

interface HasBadge
{
    public function label(): string;

    public function color(): string;
}
