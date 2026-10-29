<?php

namespace Modules\Missions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionAsset;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Missions\Actions\AddMissionEvent;
use Modules\Missions\Actions\AddMissionParticipant;
use Modules\Missions\Actions\AssignMissionAsset;
use Modules\Missions\Actions\AuthorizeMission;
use Modules\Missions\Actions\CloseMission;
use Modules\Missions\Actions\CompleteMission;
use Modules\Missions\Actions\ComputeMissionReview;
use Modules\Missions\Actions\ImportPatrolTrack;
use Modules\Missions\Actions\RecordAssetUsage;
use Modules\Missions\Actions\RecordObservedUnit;
use Modules\Missions\Actions\RecordObserverBoarding;
use Modules\Missions\Actions\SaveAsset;
use Modules\Missions\Actions\SaveMissionBriefing;
use Modules\Missions\Actions\SaveMissionDebriefing;
use Modules\Missions\Actions\StartMission;
use Modules\Missions\Http\Requests\AssetAssignmentRequest;
use Modules\Missions\Http\Requests\AssetRequest;
use Modules\Missions\Http\Requests\AssetUsageRequest;
use Modules\Missions\Http\Requests\BriefingRequest;
use Modules\Missions\Http\Requests\DebriefingRequest;
use Modules\Missions\Http\Requests\EventRequest;
use Modules\Missions\Http\Requests\ObservedUnitRequest;
use Modules\Missions\Http\Requests\ObserverBoardingRequest;
use Modules\Missions\Http\Requests\ParticipantRequest;
use Modules\Missions\Http\Requests\TrackRequest;

class MissionRecordController extends Controller
{
    public function authorizeMission(Mission $mission, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AuthorizeMission::class, $request->user(), ['mission_id' => $mission->id]);

        return $this->back($mission, 'authorized')->with('popup', true);
    }

    public function start(Mission $mission, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(StartMission::class, $request->user(), ['mission_id' => $mission->id]);

        return $this->back($mission, 'started')->with('popup', true);
    }

    public function complete(Mission $mission, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CompleteMission::class, $request->user(), ['mission_id' => $mission->id]);

        return $this->back($mission, 'completed')->with('popup', true);
    }

    public function close(Mission $mission, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CloseMission::class, $request->user(), ['mission_id' => $mission->id]);

        return $this->back($mission, 'closed')->with('popup', true);
    }

    public function review(Mission $mission, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(ComputeMissionReview::class, $request->user(), ['mission_id' => $mission->id]);

        return $this->back($mission, 'review');
    }

    public function participant(Mission $mission, ParticipantRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AddMissionParticipant::class, $request->user(), [...$request->validated(), 'mission_id' => $mission->id]);

        return $this->back($mission, 'participant');
    }

    public function asset(Mission $mission, AssetAssignmentRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AssignMissionAsset::class, $request->user(), [...$request->validated(), 'mission_id' => $mission->id]);

        return $this->back($mission, 'asset');
    }

    public function usage(MissionAsset $missionAsset, AssetUsageRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordAssetUsage::class, $request->user(), [...$request->validated(), 'mission_asset_id' => $missionAsset->id]);

        return $this->back($missionAsset->mission, 'usage');
    }

    public function briefing(Mission $mission, BriefingRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveMissionBriefing::class, $request->user(), [...$request->validated(), 'mission_id' => $mission->id]);

        return $this->back($mission, 'briefing');
    }

    public function debriefing(Mission $mission, DebriefingRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveMissionDebriefing::class, $request->user(), [...$request->validated(), 'mission_id' => $mission->id]);

        return $this->back($mission, 'debriefing');
    }

    public function event(Mission $mission, EventRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AddMissionEvent::class, $request->user(), [...$request->validated(), 'mission_id' => $mission->id]);

        return $this->back($mission, 'event');
    }

    public function unit(Mission $mission, ObservedUnitRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordObservedUnit::class, $request->user(), [...$request->validated(), 'mission_id' => $mission->id]);

        return $this->back($mission, 'unit');
    }

    public function track(Mission $mission, TrackRequest $request, ActionRunner $actions, DocumentStore $documents): RedirectResponse
    {
        $actions->run(ImportPatrolTrack::class, $request->user(), [
            'mission_id' => $mission->id,
            'document_id' => $documents->store($request->file('file'), $request->user())->id,
        ]);

        return $this->back($mission, 'track');
    }

    public function observer(Mission $mission, ObserverBoardingRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordObserverBoarding::class, $request->user(), [...$request->validated(), 'mission_id' => $mission->id]);

        return $this->back($mission, 'observer');
    }

    public function storeAsset(AssetRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveAsset::class, $request->user(), $request->validated());

        return to_route('missions.index')->with('status', __('missions::missions.messages.asset_saved'));
    }

    private function back(Mission $mission, string $key): RedirectResponse
    {
        return to_route('missions.show', $mission)->with('status', __("missions::missions.messages.{$key}"));
    }
}
