<?php

namespace Modules\Infractions\Actions;

use App\Models\InfractionType;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class SaveInfractionType extends Action
{
    public function code(): string
    {
        return 'infractions.save_type';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManageTypes;
    }

    public function handle(ActionContext $context, array $input): InfractionType
    {
        $attributes = [
            ...Arr::only($input, ['code', 'label', 'legal_basis', 'severity', 'potential_penalty']),
            'is_active' => (bool) ($input['is_active'] ?? false),
        ];

        if (isset($input['infraction_type_id'])) {
            $type = InfractionType::findOrFail($input['infraction_type_id']);
            $type->update($attributes);

            return $type;
        }

        return InfractionType::create($attributes);
    }
}
