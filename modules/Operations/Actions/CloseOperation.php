<?php

namespace Modules\Operations\Actions;

use App\Enums\LogbookDayStatus;
use App\Enums\LogbookHandlingStatus;
use App\Enums\OperationMilestone;
use App\Enums\OperationStatus;
use App\Models\LogbookEntry;
use App\Models\Operation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\LogbookWriter;
use App\Support\Printing\PdfRenderer;
use App\Support\Watch\WatchList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Modules\Operations\Support\OperationDocument;

class CloseOperation extends Action
{
    public function __construct(
        private readonly WatchList $watchList,
        private readonly LogbookWriter $logbook,
        private readonly LogbookChannels $channels,
        private readonly OperationDocument $document,
        private readonly PdfRenderer $pdf,
        private readonly DocumentStore $documents,
    ) {}

    public function code(): string
    {
        return 'operations.close';
    }

    public function permission(): Permission
    {
        return Permission::OperationsClose;
    }

    public function handle(ActionContext $context, array $input): Operation
    {
        $operation = Operation::with('vessels')->findOrFail($input['operation_id']);

        if ($operation->isFinished()) {
            throw ValidationException::withMessages(['result' => __('operations::operations.errors.closed')]);
        }

        $operation->update([
            'status' => OperationStatus::Closed,
            'result' => $input['result'],
            'infractions_noted' => filled($input['infractions_noted'] ?? null) ? $input['infractions_noted'] : null,
            'ended_at' => now(),
            'closed_by' => $context->actor->id,
        ]);

        LogbookEntry::query()
            ->where('operation_id', $operation->id)
            ->where('operation_milestone', OperationMilestone::Opened)
            ->whereHas('day', fn (Builder $days) => $days->where('status', LogbookDayStatus::Open))
            ->update(['handling_status' => LogbookHandlingStatus::Closed]);

        $this->logbook->write($context, [
            'channel_id' => $input['channel_id'] ?? $this->channels->idFor(LogbookChannels::INTERNAL),
            'direction' => $input['direction'] ?? null,
            'content' => $input['result'],
            'operation_id' => $operation->id,
            'operation_milestone' => OperationMilestone::Closed,
            'handling_status' => LogbookHandlingStatus::Closed,
            'vessel_ids' => $operation->vessels->pluck('id')->all(),
            'opens_watch' => false,
        ]);

        $this->watchList->closeForOperation($operation);

        $printed = PdfRenderer::inPrintLocale(fn () => $this->pdf->render($this->document->build($operation->fresh())));
        $this->documents->attach(
            $this->documents->storeContent(OperationDocument::basename($operation).'.pdf', $printed, 'application/pdf', $context->actor, freeze: true),
            $operation,
        );

        foreach ($operation->vessels as $vessel) {
            $context->publish(
                vessel: $vessel,
                module: 'operations',
                type: 'operation.closed',
                subject: $operation,
                summaryKey: 'operations::events.closed',
                params: ['code' => $operation->code],
            );
        }

        return $operation;
    }
}
