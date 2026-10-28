<?php

namespace Modules\Movements\Services;

use App\Enums\BeaconStatus;
use App\Enums\EezCheckResult;
use App\Enums\EezCheckType;
use App\Enums\EezReason;
use App\Enums\LicenseRecordStatus;
use App\Models\EezDeclaration;
use App\Models\License;
use App\Models\Vessel;

class EezChecker
{
    /** @return array<int, array{check_type: EezCheckType, result: EezCheckResult, comment: ?string}> */
    public function run(EezDeclaration $declaration): array
    {
        $vessel = $declaration->vessel;

        return collect(EezCheckType::cases())
            ->map(fn (EezCheckType $type) => [
                'check_type' => $type,
                ...$this->evaluate($type, $declaration, $vessel),
            ])
            ->all();
    }

    /** @return array{result: EezCheckResult, comment: ?string} */
    private function evaluate(EezCheckType $type, EezDeclaration $declaration, ?Vessel $vessel): array
    {
        return match ($type) {
            EezCheckType::Ais, EezCheckType::Dpep72h, EezCheckType::Access, EezCheckType::InnLists => $this->notApplicable('source_unavailable'),
            EezCheckType::Vms => $vessel ? $this->vms($vessel) : $this->notApplicable('unknown_vessel'),
            EezCheckType::License => $vessel ? $this->license($vessel, $declaration) : $this->notApplicable('unknown_vessel'),
            EezCheckType::DesignatedPort => $this->designatedPort($declaration),
            EezCheckType::Consistency => $vessel ? $this->consistency($vessel, $declaration) : $this->notApplicable('unknown_vessel'),
        };
    }

    private function vms(Vessel $vessel): array
    {
        return match ($vessel->beaconStatus()) {
            BeaconStatus::Emitting => $this->result(true),
            BeaconStatus::Silent => $this->result(false, 'silent_beacon'),
            BeaconStatus::NoData => $this->result(false, 'no_beacon'),
        };
    }

    private function license(Vessel $vessel, EezDeclaration $declaration): array
    {
        $day = ($declaration->crossed_at ?? $declaration->received_at)->toDateString();

        $valid = License::where('vessel_id', $vessel->id)
            ->where('status', LicenseRecordStatus::Active->value)
            ->where(fn ($query) => $query->whereNull('issued_on')->orWhereDate('issued_on', '<=', $day))
            ->whereDate('expires_on', '>=', $day)
            ->exists();

        return $this->result($valid, 'no_valid_license');
    }

    private function designatedPort(EezDeclaration $declaration): array
    {
        if ($declaration->reason !== EezReason::IvorianPortTransit) {
            return $this->notApplicable('not_port_transit');
        }

        $designated = collect(config('surveillance.eez_designated_localities'))
            ->contains(fn (string $locality) => filled($declaration->destination) && str_contains(mb_strtolower($declaration->destination), mb_strtolower($locality)));

        return $this->result($designated, 'destination_not_designated');
    }

    private function consistency(Vessel $vessel, EezDeclaration $declaration): array
    {
        $comparisons = [
            [$declaration->declared_name, $vessel->name],
            [$declaration->declared_imo, $vessel->imo_number],
            [$declaration->declared_ircs, $vessel->call_sign],
            [$declaration->declared_owner, $vessel->owner],
        ];

        $mismatch = collect($comparisons)->contains(fn (array $pair) => filled($pair[0]) && filled($pair[1]) && mb_strtoupper(trim($pair[0])) !== mb_strtoupper(trim($pair[1])));

        if (filled($declaration->declared_flag) && ! in_array(mb_strtoupper(trim($declaration->declared_flag)), [mb_strtoupper($vessel->flag), mb_strtoupper($vessel->flagName())], true)) {
            $mismatch = true;
        }

        return $this->result(! $mismatch, 'identity_mismatch');
    }

    private function result(bool $compliant, ?string $failure = null): array
    {
        return ['result' => $compliant ? EezCheckResult::Ok : EezCheckResult::Ko, 'comment' => $compliant ? null : $failure];
    }

    private function notApplicable(string $reason): array
    {
        return ['result' => EezCheckResult::NotApplicable, 'comment' => $reason];
    }
}
