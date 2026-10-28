<?php

namespace Modules\Logbook\Support;

use App\Models\LogbookDay;
use App\Models\LogbookEntry;
use App\Models\Operation;

final class LogbookCounters
{
    /** @return array<string, mixed> */
    public function for(LogbookDay $day): array
    {
        $entries = LogbookEntry::query()->where('logbook_day_id', $day->id)->with('channel')->get();

        return [
            'entries_total' => $entries->count(),
            'received' => $entries->where('direction.value', 'received')->count(),
            'sent' => $entries->where('direction.value', 'sent')->count(),
            'orders_or_decisions' => $entries->where('is_order_or_decision', true)->count(),
            'corrections' => $entries->whereNotNull('corrects_entry_id')->count(),
            'by_handling_status' => $entries->countBy(fn (LogbookEntry $entry) => $entry->handling_status->value)->all(),
            'by_channel' => $entries->countBy(fn (LogbookEntry $entry) => $entry->channel->code)->all(),
            'operations_by_category' => Operation::query()
                ->whereBetween('started_at', [$day->shift_start_at, $day->shift_start_at->copy()->addDay()])
                ->with('category')
                ->get()
                ->countBy(fn (Operation $operation) => $operation->category->code->value)
                ->all(),
        ];
    }
}
