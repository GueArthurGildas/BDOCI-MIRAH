<?php

namespace App\Support\Logbook;

use App\Models\LogbookDay;
use App\Models\LogbookEntry;

final class LogbookCounters
{
    public function __construct(private readonly OperationSnapshot $operations) {}

    /** @return array<string, mixed> */
    public function for(LogbookDay $day): array
    {
        $entries = LogbookEntry::query()->where('logbook_day_id', $day->id)->with('channel')->get();
        $from = $day->shift_start_at;
        $to = $day->shift_start_at->copy()->addDay();

        return [
            'entries_total' => $entries->count(),
            'received' => $entries->where('direction.value', 'received')->count(),
            'sent' => $entries->where('direction.value', 'sent')->count(),
            'orders_or_decisions' => $entries->where('is_order_or_decision', true)->count(),
            'corrections' => $entries->whereNotNull('corrects_entry_id')->count(),
            'by_handling_status' => $entries->countBy(fn (LogbookEntry $entry) => $entry->handling_status->value)->all(),
            'by_channel' => $entries->countBy(fn (LogbookEntry $entry) => $entry->channel->code)->all(),
            'operation_counters' => $this->operations->lastNumbers($from->year),
            'open_operations' => $this->operations->openOperations(),
            'operations_opened' => $this->operations->openedBetween($from, $to),
            'operations_closed' => $this->operations->closedBetween($from, $to),
        ];
    }
}
