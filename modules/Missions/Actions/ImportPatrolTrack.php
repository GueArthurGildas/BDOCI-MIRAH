<?php

namespace Modules\Missions\Actions;

use App\Models\Document;
use App\Models\Mission;
use App\Models\PatrolTrack;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Missions\Support\GpxTrack;

class ImportPatrolTrack extends Action
{
    public function __construct(private readonly DocumentStore $documents) {}

    public function code(): string
    {
        return 'missions.import_track';
    }

    public function permission(): Permission
    {
        return Permission::MissionsManage;
    }

    public function handle(ActionContext $context, array $input): PatrolTrack
    {
        $mission = Mission::findOrFail($input['mission_id']);
        $document = Document::findOrFail($input['document_id']);
        $track = GpxTrack::parse(Storage::disk(DocumentStore::DISK)->get($document->path));

        $patrolTrack = $mission->tracks()->create(['document_id' => $document->id]);
        $this->documents->attach($document, $mission);

        DB::statement('UPDATE patrol_tracks SET geometry = ST_SetSRID(ST_GeomFromText(?), 4326) WHERE id = ?', [$track->wkt(), $patrolTrack->id]);

        $crossed = DB::table('zones')
            ->whereRaw('ST_Intersects(ST_SetSRID(ST_GeomFromGeoJSON(zones.geometry::text), 4326), (SELECT geometry FROM patrol_tracks WHERE id = ?))', [$patrolTrack->id])
            ->get(['id', 'name'])
            ->map(fn (object $zone) => ['id' => $zone->id, 'name' => $zone->name])
            ->all();

        $patrolTrack->update(['zones_crossed' => $crossed]);

        return $patrolTrack;
    }
}
