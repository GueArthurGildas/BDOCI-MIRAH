<?php

namespace Modules\Registry\Support;

use Closure;

final class Field
{
    private function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $required,
        public readonly ?Closure $options,
        public readonly ?string $uniqueColumn,
        public readonly array $rules,
    ) {}

    public static function text(string $name, bool $required = false, bool $unique = false, int $max = 190): self
    {
        return new self($name, 'text', $required, null, $unique ? $name : null, ['string', "max:{$max}"]);
    }

    public static function email(string $name): self
    {
        return new self($name, 'email', false, null, null, ['email', 'max:190']);
    }

    public static function number(string $name, bool $required = false, string $step = '1', array $rules = ['numeric']): self
    {
        return new self($name, 'number:'.$step, $required, null, null, $rules);
    }

    public static function date(string $name): self
    {
        return new self($name, 'date', false, null, null, ['date']);
    }

    public static function textarea(string $name): self
    {
        return new self($name, 'textarea', false, null, null, ['string']);
    }

    public static function checkbox(string $name): self
    {
        return new self($name, 'checkbox', false, null, null, ['boolean']);
    }

    public static function select(string $name, Closure $options, bool $required = false): self
    {
        return new self($name, 'select', $required, $options, null, []);
    }

    /** @return array<int|string, string> */
    public function optionList(): array
    {
        return $this->options ? ($this->options)() : [];
    }
}
