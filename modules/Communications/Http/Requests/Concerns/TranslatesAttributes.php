<?php

namespace Modules\Communications\Http\Requests\Concerns;

trait TranslatesAttributes
{
    /** @return array<string, string> */
    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __("communications::fields.{$field}")])
            ->all();
    }
}
