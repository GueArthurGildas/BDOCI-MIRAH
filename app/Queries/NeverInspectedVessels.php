<?php

namespace App\Queries;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class NeverInspectedVessels
{
    public static function query(): Builder
    {
        $lastSeen = DB::table('observed_units')
            ->selectRaw('vessel_id, max(observed_at) as last_seen_at')
            ->whereNotNull('vessel_id')
            ->groupBy('vessel_id');

        return Vessel::query()
            ->joinSub(DB::table('v_seen_never_inspected'), 'seen', 'seen.vessel_id', '=', 'vessels.id')
            ->leftJoinSub($lastSeen, 'last_seen', 'last_seen.vessel_id', '=', 'vessels.id')
            ->select('vessels.*', 'seen.sighted_count', 'last_seen.last_seen_at');
    }
}
