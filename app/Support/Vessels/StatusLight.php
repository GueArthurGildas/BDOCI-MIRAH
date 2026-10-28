<?php

namespace App\Support\Vessels;

use App\Enums\CurrentLicenseStatus;
use App\Enums\VesselStatusLight;

final class StatusLight
{
    public static function assess(StatusLightFacts $facts): StatusLightAssessment
    {
        $reasons = [];
        $level = VesselStatusLight::Compliant;
        $raise = function (VesselStatusLight $to, string $key, array $params = []) use (&$level, &$reasons) {
            $reasons[] = ['key' => $key, 'params' => $params];
            $level = $to->severity() > $level->severity() ? $to : $level;
        };

        if ($facts->openInfractions > 0) {
            $raise(VesselStatusLight::InViolation, 'open_infractions', ['count' => $facts->openInfractions]);
        }

        if (in_array($facts->licenseStatus, [CurrentLicenseStatus::Expired, CurrentLicenseStatus::Suspended, CurrentLicenseStatus::Revoked], true)) {
            $raise(VesselStatusLight::Suspect, 'license_'.$facts->licenseStatus->value);
        }

        if ($facts->silent) {
            $raise(VesselStatusLight::Suspect, 'beacon_silent');
        }

        if ($facts->unexplainedVariances > 0) {
            $raise(VesselStatusLight::Suspect, 'unexplained_variances', ['count' => $facts->unexplainedVariances]);
        }

        if ($facts->checksCount >= (int) config('vigilance.status_light.checks_before_suspect')) {
            $raise(VesselStatusLight::Suspect, 'repeated_checks', ['count' => $facts->checksCount]);
        }

        if ($facts->failureOngoing) {
            $raise(VesselStatusLight::ToWatch, 'vms_failure');
        }

        if ($facts->enhancedMonitoring) {
            $raise(VesselStatusLight::ToWatch, 'enhanced_monitoring');
        }

        if ($facts->licenseStatus === CurrentLicenseStatus::Active
            && $facts->licenseDaysLeft !== null
            && $facts->licenseDaysLeft <= (int) config('vigilance.status_light.license_expiring_days')) {
            $raise(VesselStatusLight::ToWatch, 'license_expiring', ['days' => $facts->licenseDaysLeft]);
        }

        return new StatusLightAssessment($level, $reasons);
    }
}
