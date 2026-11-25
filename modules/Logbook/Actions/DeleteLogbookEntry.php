<?php

namespace Modules\Logbook\Actions;

use App\Models\LogbookEntry;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\RecordDeletion;
use Illuminate\Validation\ValidationException;

class DeleteLogbookEntry extends Action
{
    public function __construct(private readonly RecordDeletion $deletion) {}

    public function code(): string
    {
        return 'logbook.delete_entry';
    }

    public function permission(): Permission
    {
        return Permission::LogbookDelete;
    }

    public function handle(ActionContext $context, array $input): LogbookEntry
    {
        $entry = LogbookEntry::with(['day', 'observation', 'surveillanceObservation', 'witnessReport', 'vessels'])->findOrFail($input['entry_id']);
        $this->deletion->ensureDeletable($entry);

        if ($entry->operation_milestone !== null || $entry->observation || $entry->surveillanceObservation || $entry->witnessReport) {
            throw ValidationException::withMessages(['reason' => __('ui.deletion.linked_entry')]);
        }

        $this->deletion->cancel($entry, $context->actor, $input['reason']);

        $entry->vessels->each(fn ($vessel) => $context->publish(
            vessel: $vessel->id,
            module: 'logbook',
            type: 'entry.cancelled',
            subject: $entry,
            summaryKey: 'logbook::events.entry_cancelled',
            params: ['id' => $entry->id],
        ));

        return $entry;
    }
}
