<?php

namespace App\Support\Vms;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface PositionFeed
{
    public function source(): string;

    /** @return Collection<int, PositionSnapshot> */
    public function snapshot(Carbon $at): Collection;
}
