<?php

namespace Modules\Logbook\Http\Requests;

use App\Enums\LogbookHandlingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetLogbookEntryHandlingRequest extends FormRequest
{
    public function rules(): array
    {
        return ['handling_status' => ['required', Rule::enum(LogbookHandlingStatus::class)]];
    }

    public function attributes(): array
    {
        return ['handling_status' => __('logbook::logbook.entries.fields.handling_status')];
    }
}
