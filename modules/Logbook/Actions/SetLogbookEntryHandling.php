<?php

namespace Modules\Logbook\Actions;

use App\Enums\LogbookDayStatus;
use App\Enums\LogbookHandlingStatus;
use App\Enums\WatchOrigin;
use App\Models\LogbookEntry;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Watch\WatchList;
use Illuminate\Validation\ValidationException;

class SetLogbookEntryHandling extends Action
{
    public function __construct(private readonly WatchList $watchList) {}

    public function code(): string
    {
        return 'logbook.set_entry_handling';
    }

    public function permission(): Permission
    {
        return Permission::LogbookManage;
    }

    public function handle(ActionContext $context, array $input): LogbookEntry
    {
        $entry = LogbookEntry::with(['day', 'vessels'])->findOrFail($input['entry_id']);
        $status = LogbookHandlingStatus::from($input['handling_status']);

        if ($entry->day->status === LogbookDayStatus::Locked) {
            throw ValidationException::withMessages(['handling_status' => __('logbook::logbook.errors.day_locked')]);
        }

        $entry->update(['handling_status' => $status]);

        if ($status === LogbookHandlingStatus::Closed) {
            $this->watchList->closeForLogbookEntry($entry);
        } else {
            $entry->vessels->each(fn ($vessel) => $this->watchList->open($vessel, WatchOrigin::Logbook, $context->actor, $entry));
        }

        foreach ($entry->vessels as $vessel) {
            $context->publish(
                vessel: $vessel,
                module: 'logbook',
                type: 'entry.handled',
                subject: $entry,
                summaryKey: 'logbook::events.handled.'.$status->value,
                params: ['id' => $entry->id],
            );
        }

        return $entry;
    }
}
