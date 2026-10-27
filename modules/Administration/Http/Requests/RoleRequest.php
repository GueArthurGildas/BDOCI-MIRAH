<?php

namespace Modules\Administration\Http\Requests;

use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    private const NAME_MAX_LENGTH = 60;

    private const DESCRIPTION_MAX_LENGTH = 500;

    public function rules(): array
    {
        $role = $this->route('role');
        $isSystem = $role !== null && Role::isSystem($role->name);

        return [
            'name' => $isSystem ? ['prohibited'] : ['required', 'string', 'max:'.self::NAME_MAX_LENGTH, Rule::unique('roles', 'name')->ignore($role)],
            'description' => $isSystem ? ['prohibited'] : ['nullable', 'string', 'max:'.self::DESCRIPTION_MAX_LENGTH],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::enum(Permission::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('administration::administration.roles.name'),
            'description' => __('administration::administration.roles.description'),
        ];
    }
}
