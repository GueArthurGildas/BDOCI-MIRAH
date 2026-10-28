<?php

namespace Modules\Operations\Actions;

use App\Enums\OperationStatus;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\Operations\Support\OperationNumbering;

class OpenOperation extends Action
{
    private const CONTEXT_FIELDS = ['first_event', 'temperature', 'pressure', 'visibility', 'cloud_cover', 'wind_beaufort', 'sea_state'];

    public function __construct(private readonly OperationNumbering $numbering) {}

    public function code(): string
    {
        return 'operations.open';
    }

    public function permission(): Permission
    {
        return Permission::OperationsCreate;
    }

    public function handle(ActionContext $context, array $input): Operation
    {
        $category = OperationCategory::findOrFail($input['category_id']);
        $startedAt = isset($input['started_at']) ? Carbon::parse($input['started_at']) : now();
        $year = $startedAt->year;
        $number = $this->numbering->next($category, $year);

        $operation = Operation::create([
            ...Arr::only($input, ['name', 'origin', 'summary']),
            'category_id' => $category->id,
            'number' => $number,
            'year' => $year,
            'code' => $this->numbering->code($category, $number, $year, $input['name']),
            'status' => OperationStatus::Open,
            'started_at' => $startedAt,
            'created_by' => $context->actor->id,
        ]);

        $contextFields = array_filter(Arr::only($input, self::CONTEXT_FIELDS), fn (mixed $value) => $value !== null && $value !== '');

        if ($contextFields !== []) {
            $operation->context()->create($contextFields);
        }

        $operation->personnel()->attach($context->actor->id, ['role' => 'lead']);
        $operation->vessels()->sync($input['vessel_ids'] ?? []);

        foreach ($operation->vessels as $vessel) {
            $context->publish(
                vessel: $vessel,
                module: 'operations',
                type: 'operation.opened',
                subject: $operation,
                summaryKey: 'operations::events.opened',
                params: ['code' => $operation->code],
                occurredAt: $startedAt,
            );
        }

        return $operation;
    }
}
