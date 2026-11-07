<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvatarRequest extends FormRequest
{
    public const MAX_SIZE_KB = 2048;

    public function rules(): array
    {
        return ['avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_SIZE_KB]];
    }

    public function attributes(): array
    {
        return ['avatar' => __('profile::profile.photo.field')];
    }
}
