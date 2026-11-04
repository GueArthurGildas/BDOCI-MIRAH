<?php

namespace Modules\Reports\Http\Requests;

use App\Enums\ExportFormat;
use App\Enums\OperationStatus;
use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('type')->permission()->value);
    }

    public function rules(): array
    {
        return [
            'format' => ['required', Rule::enum(ExportFormat::class)],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'status' => ['nullable', Rule::enum(OperationStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
            'record_status' => ['nullable', Rule::enum(RecordStatus::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'vessel' => ['nullable', 'integer', 'exists:vessels,id'],
        ];
    }

    public function attributes(): array
    {
        return collect(['format', 'year', 'status', 'search', 'record_status', 'from', 'to', 'vessel'])
            ->mapWithKeys(fn (string $field) => [$field => __("reports::reports.exports.fields.{$field}")])
            ->all();
    }
}
