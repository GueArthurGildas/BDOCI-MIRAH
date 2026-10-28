<?php

namespace Modules\Watch\Actions;

use App\Models\Rotation;
use App\Models\Team;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\LogbookCalendar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PlanRotations extends Action
{
    public function __construct(private readonly LogbookCalendar $calendar) {}

    public function code(): string
    {
        return 'watch.plan_rotations';
    }

    public function permission(): Permission
    {
        return Permission::WatchManage;
    }

    /** @return Collection<int, Rotation> */
    public function handle(ActionContext $context, array $input): Collection
    {
        $teams = Team::orderBy('id')->limit(config('surveillance.watch.teams_count'))->get();
        $created = collect();

        if ($teams->isEmpty()) {
            return $created;
        }

        $start = Carbon::parse($input['start_on'])->startOfDay();
        $position = $this->nextPosition($start, $teams);

        foreach (range(0, $input['days_count'] - 1) as $offset) {
            $day = $start->copy()->addDays($offset);

            if (! $this->calendar->isWorkingDay($day) || Rotation::whereDate('day', $day)->exists()) {
                continue;
            }

            $created->push(Rotation::create([
                'team_id' => $teams[$position % $teams->count()]->id,
                'day' => $day->toDateString(),
                'start_time' => sprintf('%02d:00:00', config('surveillance.watch.shift_start_hour')),
                'end_time' => sprintf('%02d:00:00', config('surveillance.watch.shift_end_hour')),
            ]));

            $position++;
        }

        return $created;
    }

    private function nextPosition(Carbon $start, Collection $teams): int
    {
        $previous = Rotation::whereDate('day', '<', $start)->orderByDesc('day')->first();

        if ($previous === null) {
            return 0;
        }

        return ($teams->search(fn (Team $team) => $team->id === $previous->team_id) ?: 0) + 1;
    }
}
