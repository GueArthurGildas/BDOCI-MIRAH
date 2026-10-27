<?php

namespace App\Support\Settings;

final readonly class SettingDefinition
{
    public function __construct(
        public string $key,
        public string $configKey,
        public SettingType $type,
        public string $group,
        public int $min = 0,
        public int $max = 0,
    ) {}

    public function field(): string
    {
        return str_replace('.', '_', $this->key);
    }

    public function cast(mixed $raw): int|bool
    {
        return match ($this->type) {
            SettingType::Integer => (int) $raw,
            SettingType::Boolean => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
        };
    }

    /** @return array<int, string> */
    public function rules(): array
    {
        return match ($this->type) {
            SettingType::Integer => ['required', 'integer', "min:{$this->min}", "max:{$this->max}"],
            SettingType::Boolean => ['required', 'boolean'],
        };
    }
}
