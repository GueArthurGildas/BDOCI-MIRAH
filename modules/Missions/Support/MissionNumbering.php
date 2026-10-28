<?php

namespace Modules\Missions\Support;

use App\Support\Numbering\YearlySequence;

final class MissionNumbering
{
    private const LOCK_KEY = 6003;

    public function __construct(private readonly YearlySequence $sequence) {}

    public function code(int $year): string
    {
        return $this->sequence->next('MIS', $year, 'missions', 'code', self::LOCK_KEY);
    }
}
