<?php

namespace Modules\Witnesses\Http\Requests;

class OperationLinkRequest extends WitnessesRequest
{
    public function rules(): array
    {
        return [
            'operation_id' => ['required', 'exists:operations,id'],
        ];
    }
}
