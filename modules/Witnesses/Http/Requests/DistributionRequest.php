<?php

namespace Modules\Witnesses\Http\Requests;

class DistributionRequest extends WitnessesRequest
{
    public function rules(): array
    {
        return [
            'authorized_list_publication_id' => ['required', 'exists:authorized_list_publications,id'],
            'recipient' => ['required', 'string', 'max:190'],
        ];
    }
}
