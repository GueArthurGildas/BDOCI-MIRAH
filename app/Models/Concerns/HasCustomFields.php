<?php

namespace App\Models\Concerns;

use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use InvalidArgumentException;

trait HasCustomFields
{
    public function customField(string $code): ?string
    {
        $definition = $this->customFieldDefinition($code);

        return $definition === null
            ? null
            : CustomFieldValue::query()
                ->where('custom_field_definition_id', $definition->id)
                ->where('subject_id', $this->getKey())
                ->value('value');
    }

    public function setCustomField(string $code, ?string $value): void
    {
        $definition = $this->customFieldDefinition($code);

        throw_if($definition === null, InvalidArgumentException::class, "Unknown custom field [{$code}].");

        CustomFieldValue::updateOrCreate(
            ['custom_field_definition_id' => $definition->id, 'subject_id' => $this->getKey()],
            ['value' => $value],
        );
    }

    private function customFieldDefinition(string $code): ?CustomFieldDefinition
    {
        return CustomFieldDefinition::query()
            ->where('subject_type', $this->getMorphClass())
            ->where('code', $code)
            ->first();
    }
}
