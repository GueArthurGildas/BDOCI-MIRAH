<?php

namespace Modules\Profile\Support;

use App\Enums\AlertCategory;
use App\Enums\NoteUrgency;
use App\Enums\OperationStatus;
use App\Models\InternalNote;
use App\Models\Observation;
use App\Models\Operation;
use App\Models\User;
use App\Support\Alerts\AlertTriage;

final class PersonalSpace
{
    private const LIST_LIMIT = 15;

    private const MESSAGES_LIMIT = 30;

    /** @return array<string, mixed> */
    public static function for(User $user): array
    {
        $alerts = AlertTriage::latest($user, self::LIST_LIMIT);

        return [
            'messages' => InternalNote::addressedTo($user)->with(['author', 'vessel'])->orderBy('is_read')->latest('id')->limit(self::MESSAGES_LIMIT)->get(),
            'unreadMessages' => InternalNote::addressedTo($user)->where('is_read', false)->count(),
            'alerts' => $alerts,
            'alertCounts' => AlertTriage::counts($user),
            'urgentAlerts' => $alerts[AlertCategory::Urgent->value],
            'urgentNotes' => InternalNote::addressedTo($user)->where('is_read', false)->where('urgency', NoteUrgency::High->value)->with(['author', 'vessel'])->latest('id')->limit(self::LIST_LIMIT)->get(),
            'observations' => Observation::pending()
                ->where(fn ($query) => $query->where('author_id', $user->id)->when($user->team_id, fn ($inner, int $team) => $inner->orWhere('team_id', $team)))
                ->with('vessel')->latest('observed_at')->limit(self::LIST_LIMIT)->get(),
            'dueNotes' => InternalNote::addressedTo($user)->where('is_read', false)->whereNotNull('due_date')->with(['author', 'vessel'])->orderBy('due_date')->limit(self::LIST_LIMIT)->get(),
            'operations' => Operation::query()->where('created_by', $user->id)->whereIn('status', [OperationStatus::Open->value, OperationStatus::InProgress->value])->latest('started_at')->limit(self::LIST_LIMIT)->get(),
        ];
    }
}
