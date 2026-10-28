<?php

namespace Modules\Infractions\Http\Requests;

use App\Enums\FollowUpType;
use Illuminate\Validation\Rule;

class FollowUpRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(FollowUpType::class)],
        ];
    }
}
