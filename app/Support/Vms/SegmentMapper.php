<?php

namespace App\Support\Vms;

use App\Enums\FleetSegment;
use App\Enums\VesselType;
use App\Enums\ZoneRuleSegment;
use App\Models\Vessel;

final class SegmentMapper
{
    public function for(Vessel $vessel): ?ZoneRuleSegment
    {
        return match (true) {
            $vessel->is_tuna_vessel, $vessel->type === VesselType::Longliner => ZoneRuleSegment::TunaVessel,
            $vessel->type === VesselType::Trawler => ZoneRuleSegment::Trawl,
            $vessel->type === VesselType::Seiner => ZoneRuleSegment::SmallPelagics,
            $vessel->type === VesselType::Canoe, $vessel->fleet_segment === FleetSegment::Artisanal => ZoneRuleSegment::Artisanal,
            default => null,
        };
    }
}
