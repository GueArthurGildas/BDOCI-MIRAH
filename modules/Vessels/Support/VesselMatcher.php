<?php

namespace Modules\Vessels\Support;

use App\Enums\IdentifierType;
use App\Enums\MatchingCriterion;
use App\Enums\MatchingStatus;
use App\Models\Country;
use App\Models\Vessel;
use App\Models\VesselIdentifier;

class VesselMatcher
{
    private const IDENTIFIER_ORDER = [
        [IdentifierType::Imo, MatchingCriterion::Imo],
        [IdentifierType::Mmsi, MatchingCriterion::Mmsi],
        [IdentifierType::Ircs, MatchingCriterion::Ircs],
        [IdentifierType::Registration, MatchingCriterion::Registration],
    ];

    /** @param array{name?: ?string, flag?: ?string, identifiers?: array<string, ?string>} $record */
    public function match(array $record): MatchResult
    {
        $identifiers = array_filter($record['identifiers'] ?? [], filled(...));

        $hits = [];

        foreach (self::IDENTIFIER_ORDER as [$type, $criterion]) {
            $value = $identifiers[$type->value] ?? null;

            if ($value === null) {
                continue;
            }

            $ids = VesselIdentifier::where('type', $type->value)->where('value', $value)->whereNull('valid_to')
                ->whereHas('vessel')->pluck('vessel_id')->unique();

            foreach ($ids as $id) {
                $hits[] = [$id, $criterion];
            }
        }

        if ($hits !== []) {
            return $this->resolveIdentifierHits($hits, $identifiers);
        }

        return $this->matchByNameAndFlag($record);
    }

    /**
     * @param  array<int, array{0: int, 1: MatchingCriterion}>  $hits
     * @param  array<string, string>  $identifiers
     */
    private function resolveIdentifierHits(array $hits, array $identifiers): MatchResult
    {
        [$firstId, $criterion] = $hits[0];
        $candidate = Vessel::findOrFail($firstId);
        $distinctVessels = collect($hits)->pluck(0)->unique();

        if ($distinctVessels->count() > 1 || $this->contradicts($candidate, $identifiers)) {
            return new MatchResult(MatchingStatus::PossibleDuplicate, $candidate, $criterion);
        }

        return new MatchResult(MatchingStatus::Matched, $candidate, $criterion);
    }

    /** @param array<string, string> $identifiers */
    private function contradicts(Vessel $candidate, array $identifiers): bool
    {
        $active = $candidate->activeIdentifiers()->get()->groupBy(fn (VesselIdentifier $identifier) => $identifier->type->value);

        foreach ($identifiers as $type => $value) {
            $known = $active->get($type);

            if ($known !== null && $known->isNotEmpty() && ! $known->contains('value', $value)) {
                return true;
            }
        }

        return false;
    }

    /** @param array{name?: ?string, flag?: ?string} $record */
    private function matchByNameAndFlag(array $record): MatchResult
    {
        $name = $this->normalize($record['name'] ?? null);
        $country = Country::where('iso2_code', strtoupper((string) ($record['flag'] ?? '')))->first();

        if ($name === '' || $country === null) {
            return new MatchResult(MatchingStatus::New);
        }

        $candidate = Vessel::where('flag_country_id', $country->id)
            ->whereRaw("regexp_replace(upper(name), '[^A-Z0-9]', '', 'g') = ?", [$name])
            ->orderBy('id')
            ->first();

        return $candidate
            ? new MatchResult(MatchingStatus::PossibleDuplicate, $candidate, MatchingCriterion::NameFlag)
            : new MatchResult(MatchingStatus::New);
    }

    private function normalize(?string $name): string
    {
        return preg_replace('/[^A-Z0-9]/', '', mb_strtoupper((string) $name)) ?? '';
    }
}
