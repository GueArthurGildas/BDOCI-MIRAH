<?php

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

trait HasCommunicationsLabel
{
    public function label(): string
    {
        return 'communications::enums.'.Str::snake(class_basename(static::class)).'.'.$this->value;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => __($case->label())])
            ->all();
    }
}
