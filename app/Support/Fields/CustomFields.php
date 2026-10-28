<?php

namespace App\Support\Fields;

use App\Enums\CustomFieldType;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CustomFields
{
    /** @return Collection<int, CustomFieldDefinition> */
    public function definitionsFor(string|Model $object): Collection
    {
        return CustomFieldDefinition::query()
            ->where('object_type', $this->type($object))
            ->where('is_visible', true)
            ->orderBy('label')
            ->get();
    }

    public function set(Model $object, string $code, mixed $value): CustomFieldValue
    {
        $definition = CustomFieldDefinition::query()
            ->where('object_type', $object->getMorphClass())
            ->where('code', $code)
            ->firstOrFail();

        return CustomFieldValue::updateOrCreate(
            ['custom_field_definition_id' => $definition->id, 'object_id' => $object->getKey()],
            ['value' => $this->serialize($definition, $code, $value)],
        );
    }

    /** @return array<string, mixed> */
    public function valuesFor(Model $object): array
    {
        $stored = CustomFieldValue::query()
            ->where('object_id', $object->getKey())
            ->get()
            ->keyBy('custom_field_definition_id');

        return $this->definitionsFor($object)
            ->mapWithKeys(fn (CustomFieldDefinition $definition) => [
                $definition->code => $this->cast($definition, $stored->get($definition->id)?->value),
            ])
            ->all();
    }

    private function type(string|Model $object): string
    {
        return $object instanceof Model ? $object->getMorphClass() : $object;
    }

    private function serialize(CustomFieldDefinition $definition, string $code, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $valid = match ($definition->type) {
            CustomFieldType::Number => is_numeric($value),
            CustomFieldType::Date => $this->isDate($value),
            CustomFieldType::Boolean => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
            default => is_scalar($value),
        };

        if (! $valid) {
            throw ValidationException::withMessages([$code => __('validation.custom_field_invalid', ['attribute' => $definition->label])]);
        }

        return match ($definition->type) {
            CustomFieldType::Boolean => $value ? '1' : '0',
            CustomFieldType::Date => Carbon::parse($value)->toDateString(),
            default => (string) $value,
        };
    }

    private function isDate(mixed $value): bool
    {
        try {
            Carbon::parse($value);

            return is_string($value) || $value instanceof \DateTimeInterface;
        } catch (\Throwable) {
            return false;
        }
    }

    private function cast(CustomFieldDefinition $definition, ?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($definition->type) {
            CustomFieldType::Number => $value + 0,
            CustomFieldType::Boolean => $value === '1',
            CustomFieldType::Date => Carbon::parse($value),
            default => $value,
        };
    }
}
