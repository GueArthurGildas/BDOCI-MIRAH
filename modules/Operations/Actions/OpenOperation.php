<?php

namespace Modules\Operations\Actions;

use App\Enums\LogbookHandlingStatus;
use App\Enums\OperationMilestone;
use App\Enums\OperationStatus;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\CspManager;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\LogbookWriter;
use App\Support\Printing\PdfRenderer;
use App\Support\Vessels\VesselSheet;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\Operations\Support\OperationNumbering;

class OpenOperation extends Action
{
    public const LEAD_ROLE = 'lead';

    public const MANAGER_ROLE = 'manager';

    private const CONTEXT_FIELDS = ['first_event', 'temperature', 'pressure', 'visibility', 'cloud_cover', 'wind_beaufort', 'sea_state', 'weather_notes', 'vessel_position', 'vessel_activity', 'comments'];

    public function __construct(
        private readonly OperationNumbering $numbering,
        private readonly LogbookWriter $logbook,
        private readonly LogbookChannels $channels,
        private readonly VesselSheet $sheets,
        private readonly DocumentStore $documents,
        private readonly CspManager $manager,
    ) {}

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
            'csp_manager_name' => $this->manager->name(),
        ]);

        $contextFields = array_filter(Arr::only($input, self::CONTEXT_FIELDS), fn (mixed $value) => $value !== null && $value !== '');

        if ($contextFields !== []) {
            $operation->context()->create($contextFields);
        }

        $operation->personnel()->attach($context->actor->id, ['role' => self::LEAD_ROLE]);

        $operation->vessels()->sync($input['vessel_ids'] ?? []);

        $this->logbook->write($context, [
            'occurred_at' => $startedAt,
            'channel_id' => $input['channel_id'] ?? $this->channels->idFor(LogbookChannels::INTERNAL),
            'direction' => $input['direction'] ?? null,
            'content' => filled($input['summary'] ?? null) ? $input['summary'] : $operation->name,
            'operation_id' => $operation->id,
            'operation_milestone' => OperationMilestone::Opened,
            'handling_status' => LogbookHandlingStatus::InProgress,
            'vessel_ids' => $operation->vessels->pluck('id')->all(),
        ]);

        foreach ($operation->vessels as $vessel) {
            $this->documents->attach(
                $this->documents->storeContent($this->sheets->filename($vessel), PdfRenderer::inPrintLocale(fn () => $this->sheets->render($vessel, $context->actor)), 'application/pdf', $context->actor, freeze: true),
                $operation,
            );

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
