<?php

namespace App\Support\Vessels;

use App\Enums\CurrentLicenseStatus;

final class VigilanceScore
{
    public const LOW = 'low';

    public const MODERATE = 'moderate';

    public const HIGH = 'high';

    /** @return array{total: int, level: string, components: array<int, array{key: string, occurrences: int, points: int}>} */
    public static function compute(VesselIndicators $indicators): array
    {
        $occurrences = self::occurrences($indicators);

        $components = collect(config('vigilance.score.components'))
            ->map(fn (array $rule, string $key) => [
                'key' => $key,
                'occurrences' => $occurrences[$key],
                'points' => min($rule['cap'], $occurrences[$key] * $rule['points']),
            ])
            ->values()
            ->all();

        $total = min((int) config('vigilance.score.maximum'), array_sum(array_column($components, 'points')));

        return ['total' => $total, 'level' => self::level($total), 'components' => $components];
    }

    public static function level(int $total): string
    {
        return match (true) {
            $total >= config('vigilance.score.levels.high') => self::HIGH,
            $total >= config('vigilance.score.levels.moderate') => self::MODERATE,
            default => self::LOW,
        };
    }

    public static function color(string $level): string
    {
        return match ($level) {
            self::HIGH => 'danger',
            self::MODERATE => 'warning',
            default => 'success',
        };
    }

    /** @return array<string, int> */
    private static function occurrences(VesselIndicators $indicators): array
    {
        $facts = $indicators->facts;
        $licenseBlocked = in_array($facts->licenseStatus, [CurrentLicenseStatus::Expired, CurrentLicenseStatus::Suspended, CurrentLicenseStatus::Revoked], true);
        $licenseExpiring = $facts->licenseStatus === CurrentLicenseStatus::Active
            && $facts->licenseDaysLeft !== null
            && $facts->licenseDaysLeft <= (int) config('vigilance.status_light.license_expiring_days');

        return [
            'silence_now' => (int) $facts->silent,
            'silences_12_months' => $indicators->silences12Months,
            'license_blocked' => (int) $licenseBlocked,
            'license_expiring' => (int) $licenseExpiring,
            'open_infractions' => $facts->openInfractions,
            'infractions_12_months' => $indicators->infractions12Months,
            'checks' => (int) ($facts->checksCount >= (int) config('vigilance.status_light.checks_before_suspect')),
            'unexplained_variances' => $facts->unexplainedVariances,
            'unanswered_messages' => $indicators->unansweredMessages,
            'no_inspection' => (int) ($indicators->positionKnown && $indicators->inspections12Months === 0),
        ];
    }
}
