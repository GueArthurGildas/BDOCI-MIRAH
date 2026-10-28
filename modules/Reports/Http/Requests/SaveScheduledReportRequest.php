<?php

namespace Modules\Reports\Http\Requests;

use App\Enums\ReportFormat;
use App\Enums\ReportFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Reports\Support\ReportCatalog;

class SaveScheduledReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', Rule::in(app(ReportCatalog::class)->codes())],
            'frequency' => ['required', Rule::enum(ReportFrequency::class)],
            'format' => ['required', Rule::enum(ReportFormat::class)],
            'recipient_id' => ['required', Rule::exists('users', 'id')->where('is_active', true)->where('type', 'human')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return collect(['code', 'frequency', 'format', 'recipient_id', 'is_active'])
            ->mapWithKeys(fn (string $field) => [$field => __("reports::reports.scheduled.fields.{$field}")])
            ->all();
    }
}
