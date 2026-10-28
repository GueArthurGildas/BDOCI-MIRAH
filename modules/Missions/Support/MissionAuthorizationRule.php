<?php

namespace Modules\Missions\Support;

use App\Enums\MissionType;

final class MissionAuthorizationRule
{
    public static function teamLeadMayAuthorize(MissionType $type): bool
    {
        return (bool) config("surveillance.missions.team_lead_authorizes.{$type->value}");
    }
}
