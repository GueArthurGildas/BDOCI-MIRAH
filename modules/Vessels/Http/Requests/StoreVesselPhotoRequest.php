<?php

namespace Modules\Vessels\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVesselPhotoRequest extends FormRequest
{
    public const MAX_FILES = 10;

    public const MAX_SIZE_KB = 5120;

    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:1', 'max:'.self::MAX_FILES],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_SIZE_KB],
        ];
    }

    public function attributes(): array
    {
        return ['photos' => __('vessels::vessels.photos.field'), 'photos.*' => __('vessels::vessels.photos.field')];
    }
}
