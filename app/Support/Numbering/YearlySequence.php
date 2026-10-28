<?php

namespace App\Support\Numbering;

use Illuminate\Support\Facades\DB;

final class YearlySequence
{
    public const PAD_LENGTH = 4;

    public function next(string $prefix, int $year, string $table, string $column, int $lockKey): string
    {
        DB::select('select pg_advisory_xact_lock(?)', [$lockKey]);

        $stem = "{$prefix}-{$year}-";
        $last = DB::table($table)->where($column, 'like', "{$stem}%")->orderByDesc($column)->value($column);
        $sequence = $last === null ? 1 : ((int) substr($last, strlen($stem))) + 1;

        return $stem.str_pad((string) $sequence, self::PAD_LENGTH, '0', STR_PAD_LEFT);
    }
}
