<?php

namespace Modules\Infractions\Support;

use App\Enums\InfractionCaseStatus;
use App\Models\InfractionCase;

final class CaseWorkflow
{
    private const ORDER = [
        InfractionCaseStatus::Open->value => 0,
        InfractionCaseStatus::Instruction->value => 1,
        InfractionCaseStatus::Decision->value => 2,
        InfractionCaseStatus::Closed->value => 3,
    ];

    public static function advanceTo(InfractionCase $case, InfractionCaseStatus $target): void
    {
        if ($case->isClosed() || self::ORDER[$target->value] <= self::ORDER[$case->status->value]) {
            return;
        }

        $case->update(['status' => $target]);
    }
}
