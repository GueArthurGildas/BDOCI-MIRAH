<?php

namespace Modules\Missions\Actions;

use App\Models\Mission;
use App\Models\Observer;
use App\Models\ObserverBoarding;
use App\Models\Person;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class RecordObserverBoarding extends Action
{
    public function code(): string
    {
        return 'missions.record_observer_boarding';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): ObserverBoarding
    {
        $mission = Mission::findOrFail($input['mission_id']);

        $observer = isset($input['observer_id'])
            ? Observer::findOrFail($input['observer_id'])
            : Observer::create(['person_id' => Person::create(['name' => $input['name'], 'first_name' => $input['first_name'] ?? null])->id]);

        return $mission->observerBoardings()->create([
            'observer_id' => $observer->id,
            'vessel_id' => $input['vessel_id'] ?? null,
            'started_on' => filled($input['started_on'] ?? null) ? Carbon::parse($input['started_on']) : null,
            'ended_on' => filled($input['ended_on'] ?? null) ? Carbon::parse($input['ended_on']) : null,
        ]);
    }
}
