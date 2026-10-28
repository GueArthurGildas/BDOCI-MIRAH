<?php

namespace Modules\Operations\Support;

use App\Models\OperationCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OperationNumbering
{
    public const NUMBER_PAD_LENGTH = 3;

    public function next(OperationCategory $category, int $year): int
    {
        $key = ['category_id' => $category->id, 'year' => $year];

        DB::table('operation_counters')->insertOrIgnore([...$key, 'last_number' => 0]);

        $current = DB::table('operation_counters')->where($key)->lockForUpdate()->value('last_number');

        DB::table('operation_counters')->where($key)->update(['last_number' => $current + 1]);

        return $current + 1;
    }

    public function code(OperationCategory $category, int $number, int $year, string $name): string
    {
        return sprintf(
            'OPS-%s-%s-%d-%s',
            str_pad((string) $number, self::NUMBER_PAD_LENGTH, '0', STR_PAD_LEFT),
            strtoupper($category->code->value),
            $year,
            $this->nameSegment($name),
        );
    }

    private function nameSegment(string $name): string
    {
        $ascii = preg_replace('/[^A-Za-z0-9 _-]+/', '', Str::ascii($name));

        return preg_replace('/\s+/', '_', trim($ascii));
    }
}
