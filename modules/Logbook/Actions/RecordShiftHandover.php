<?php

namespace Modules\Logbook\Actions;

use App\Enums\LogbookDayStatus;
use App\Models\ShiftHandover;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\CspManager;
use App\Support\Logbook\LogbookCalendar;
use App\Support\Logbook\OperationSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RecordShiftHandover extends Action
{
    public function __construct(private readonly LogbookCalendar $calendar, private readonly OperationSnapshot $operations, private readonly CspManager $manager) {}

    public function code(): string
    {
        return 'logbook.record_handover';
    }

    public function permission(): Permission
    {
        return Permission::LogbookHandover;
    }

    public function handle(ActionContext $context, array $input): ShiftHandover
    {
        $handedOverAt = isset($input['handed_over_at']) ? Carbon::parse($input['handed_over_at']) : now();
        $day = $this->calendar->dayFor($handedOverAt);

        if ($day->status !== LogbookDayStatus::Open) {
            throw ValidationException::withMessages(['handed_over_at' => __('logbook::logbook.errors.day_not_open')]);
        }

        if ((int) $input['taken_over_by'] === $context->actor->id) {
            throw ValidationException::withMessages(['taken_over_by' => __('logbook::logbook.errors.handover_to_self')]);
        }

        $slot = $this->calendar->handoverSlot($handedOverAt);
        $alreadyDone = ShiftHandover::query()
            ->where('logbook_day_id', $day->id)
            ->get()
            ->contains(fn (ShiftHandover $handover) => $this->calendar->handoverSlot($handover->handed_over_at)->equalTo($slot));

        if ($alreadyDone) {
            throw ValidationException::withMessages(['handed_over_at' => __('logbook::logbook.errors.handover_already_done', ['hour' => $slot->format('H:i')])]);
        }

        return ShiftHandover::create([
            'logbook_day_id' => $day->id,
            'started_at' => $handedOverAt,
            'handed_over_at' => $handedOverAt,
            'handed_over_by' => $context->actor->id,
            'taken_over_by' => $input['taken_over_by'],
            'supervisor_name' => $this->manager->name(),
            'operation_counters' => $this->operations->lastNumbers($handedOverAt->year),
            'open_operations' => $this->operations->openOperations(),
            'instructions' => filled($input['instructions'] ?? null) ? $input['instructions'] : null,
        ]);
    }
}
