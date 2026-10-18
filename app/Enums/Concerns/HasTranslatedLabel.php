<?php

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

trait HasTranslatedLabel
{
    public function label(): string
    {
        $group = Str::snake(class_basename(static::class));

        return "enums.{$group}.{$this->value}";
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => __($case->label())])
            ->all();
    }
}
