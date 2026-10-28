<?php

namespace Modules\Administration\Http\Requests;

use App\Support\Authorization\PermissionGroup;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'team_id' => ['nullable', 'exists:teams,id'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp_number' => ['nullable', 'string', 'max:40'],
            'role' => ['required', Rule::enum(Role::class)],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['string', Rule::enum(PermissionGroup::class)],
            'is_active' => ['boolean', Rule::when($user?->is($this->user()), ['accepted'])],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return ['is_active.accepted' => __('administration::administration.users.cannot_deactivate_self')];
    }

    public function attributes(): array
    {
        return collect(['name', 'email', 'phone', 'whatsapp_number', 'role', 'groups', 'is_active', 'password'])
            ->mapWithKeys(fn (string $field) => [$field => __("administration::administration.users.fields.{$field}")])
            ->put('team_id', __('administration::administration.users.fields.team'))
            ->all();
    }
}
