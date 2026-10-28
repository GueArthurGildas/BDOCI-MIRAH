<?php

namespace Modules\Licenses\Support;

use App\Enums\LicenseOrigin;
use App\Enums\LicenseRecordStatus;
use App\Models\License;
use App\Models\Vessel;
use App\Support\Actions\ActionContext;
use Illuminate\Support\Arr;

class LicenseWriter
{
    /** @param array<string, mixed> $input */
    public function create(ActionContext $context, array $input, LicenseOrigin $origin): License
    {
        $license = License::create([
            ...Arr::only($input, ['vessel_id', 'number', 'segment', 'issued_on', 'expires_on']),
            'status' => LicenseRecordStatus::Active,
            'entered_by' => $context->actor->id,
            'origin' => $origin,
        ]);

        $this->syncDetails($license, $input);

        Vessel::whereKey($license->vessel_id)->update(['license_not_provided' => false]);

        $context->publish(
            vessel: $license->vessel_id,
            module: 'licenses',
            type: 'license.recorded',
            subject: $license,
            summaryKey: 'licenses::events.recorded',
            params: ['number' => $license->number, 'expires_on' => $license->expires_on->toDateString()],
        );

        return $license;
    }

    /** @param array<string, mixed> $input */
    public function syncDetails(License $license, array $input): void
    {
        if (array_key_exists('zone_ids', $input)) {
            $license->zones()->sync($input['zone_ids'] ?? []);
        }

        if (array_key_exists('gear_ids', $input)) {
            $license->gears()->sync($input['gear_ids'] ?? []);
        }

        if (array_key_exists('species_ids', $input)) {
            $license->species()->sync($input['species_ids'] ?? []);
        }

        if (array_key_exists('conditions', $input)) {
            $license->conditions()->delete();

            foreach (array_filter(array_map('trim', $input['conditions'] ?? [])) as $body) {
                $license->conditions()->create(['body' => $body]);
            }
        }
    }
}
