<?php

namespace Modules\Communications\Support;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\Vessel;
use App\Queries\SilentBeacons;
use Illuminate\Support\Collection;

class TriggerEvents
{
    public const BEACON_SILENT = 'beacon_silent';

    public const LICENSE_EXPIRING = 'license_expiring';

    /** @return array<int, string> */
    public static function codes(): array
    {
        return [self::BEACON_SILENT, self::LICENSE_EXPIRING];
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::codes())->mapWithKeys(fn (string $code) => [$code => __("communications::triggers.{$code}")])->all();
    }

    /** @return Collection<int, Vessel> */
    public function vessels(string $code): Collection
    {
        return match ($code) {
            self::BEACON_SILENT => SilentBeacons::vessels(),
            self::LICENSE_EXPIRING => Vessel::whereIn('id', License::withStatus(LicenseStatus::Expiring)->pluck('vessel_id'))->get(),
            default => collect(),
        };
    }
}
