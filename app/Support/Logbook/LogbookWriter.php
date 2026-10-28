<?php

namespace App\Support\Logbook;

use App\Enums\LogbookDayStatus;
use App\Enums\LogbookDirection;
use App\Enums\LogbookHandlingStatus;
use App\Enums\WatchOrigin;
use App\Models\LogbookEntry;
use App\Support\Actions\ActionContext;
use App\Support\Watch\WatchList;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class LogbookWriter
{
    public function __construct(private readonly LogbookCalendar $calendar, private readonly WatchList $watchList) {}

    /** @param array<string, mixed> $input */
    public function write(ActionContext $context, array $input): LogbookEntry
    {
        $occurredAt = isset($input['occurred_at']) ? Carbon::parse($input['occurred_at']) : now();
        $day = $this->calendar->dayFor($occurredAt);

        if ($day->status !== LogbookDayStatus::Open) {
            throw ValidationException::withMessages(['occurred_at' => __('logbook::logbook.errors.day_not_open')]);
        }

        $entry = LogbookEntry::create([
            'logbook_day_id' => $day->id,
            'occurred_at' => $occurredAt,
            'channel_id' => $input['channel_id'],
            'content' => $input['content'],
            'direction' => $input['direction'] ?? LogbookDirection::Received,
            'is_order_or_decision' => $input['is_order_or_decision'] ?? false,
            'operation_id' => $input['operation_id'] ?? null,
            'handling_status' => LogbookHandlingStatus::ToHandle,
            'corrects_entry_id' => $input['corrects_entry_id'] ?? null,
            'entered_by' => $context->actor->id,
        ]);

        $entry->vessels()->sync($input['vessel_ids'] ?? []);

        foreach ($entry->vessels as $vessel) {
            if ($entry->corrects_entry_id === null) {
                $this->watchList->open($vessel, WatchOrigin::Logbook, $context->actor, $entry);
            }

            $context->publish(
                vessel: $vessel,
                module: 'logbook',
                type: 'entry.added',
                subject: $entry,
                summaryKey: 'logbook::events.entry_added',
                params: ['id' => $entry->id, 'day' => $day->day->toDateString()],
                occurredAt: $occurredAt,
            );
        }

        return $entry;
    }
}
