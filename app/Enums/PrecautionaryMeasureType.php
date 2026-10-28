<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum PrecautionaryMeasureType: string
{
    use HasTranslatedLabel;

    case Immobilization = 'immobilization';
    case PortServicesRefusal = 'port_services_refusal';
    case GearSeizure = 'gear_seizure';
    case CatchSeizure = 'catch_seizure';
    case LicenseSuspension = 'license_suspension';
    case LicenseWithdrawal = 'license_withdrawal';
    case Diversion = 'diversion';
    case Seals = 'seals';
    case CessationOrder = 'cessation_order';
}
