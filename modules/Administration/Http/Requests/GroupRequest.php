<?php

namespace Modules\Administration\Http\Requests;

use App\Support\Authorization\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupRequest extends FormRequest
{
    private const LABEL_MAX_LENGTH = 120;

    public function rules(): array
    {
        $isSystem = (bool) $this->route('group')?->is_system;

        return [
            'label' => $isSystem ? ['prohibited'] : ['required', 'string', 'max:'.self::LABEL_MAX_LENGTH],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::enum(Permission::class)],
            'members' => ['nullable', 'array'],
            'members.*' => [Rule::exists('users', 'id')],
        ];
    }

    public function attributes(): array
    {
        return ['label' => __('administration::groups.label')];
    }
}
