<?php

namespace Modules\Registry\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

final class RegistryResource
{
    /**
     * @param  class-string<Model>  $model
     * @param  array<int, Field>  $fields
     * @param  array<int, string>  $columns
     */
    public function __construct(
        public readonly string $key,
        public readonly string $model,
        public readonly string $icon,
        public readonly array $fields,
        public readonly array $columns,
        public readonly string $orderBy,
    ) {}

    /** @return array<string, array<int, mixed>> */
    public function rules(?Model $record = null): array
    {
        $table = (new $this->model)->getTable();
        $rules = [];

        foreach ($this->fields as $field) {
            $fieldRules = [$field->required ? 'required' : 'nullable', ...$field->rules];

            if ($field->type === 'select') {
                $fieldRules[] = Rule::in(array_keys($field->optionList()));
            }

            if ($field->uniqueColumn !== null) {
                $fieldRules[] = Rule::unique($table, $field->uniqueColumn)->ignore($record?->getKey());
            }

            $rules[$field->name] = $fieldRules;
        }

        return $rules;
    }

    public function field(string $name): ?Field
    {
        return collect($this->fields)->firstWhere('name', $name);
    }

    public function query()
    {
        return ($this->model)::query()->orderBy($this->orderBy);
    }
}
