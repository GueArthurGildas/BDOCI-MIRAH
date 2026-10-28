<?php

namespace App\Support\Connectors\Sources;

use App\Enums\MatchingStatus;
use App\Enums\RejectionReason;
use App\Enums\SourceSystem;
use App\Models\VesselSource;
use App\Support\Connectors\RecordedConnector;
use App\Support\Connectors\RejectedRecord;
use App\Support\Connectors\VesselLocator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

abstract class VesselFeedConnector extends RecordedConnector
{
    abstract protected function system(): SourceSystem;

    public function externalReference(array $payload): ?string
    {
        return $payload['vessel_ref'] ?? null;
    }

    public function normalise(array $payload): array
    {
        $this->require($payload, 'vessel_ref', 'name');

        $identifiers = array_filter([
            'imo' => $payload['imo'] ?? null,
            'mmsi' => $payload['mmsi'] ?? null,
            'ircs' => $payload['ircs'] ?? null,
            'registration' => $payload['registration'] ?? null,
        ]);

        if ($identifiers === []) {
            throw new RejectedRecord(RejectionReason::MissingField, 'At least one identifier (IMO, MMSI, IRCS, registration) is required');
        }

        return ['external_id' => $payload['vessel_ref'], 'identifiers' => $identifiers, 'raw' => $payload];
    }

    public function upsert(array $record, Carbon $readAt): Model
    {
        $link = VesselSource::firstOrNew(['source' => $this->system()->value, 'external_id' => $record['external_id']]);
        $link->raw_data = $record['raw'];
        $link->last_import_at = $readAt;

        if (! $link->exists || $link->matching_status === MatchingStatus::New) {
            $vessel = VesselLocator::find($record['identifiers']);
            $link->vessel_id = $vessel?->id;
            $link->matching_status = $vessel ? MatchingStatus::Matched : MatchingStatus::New;
        }

        $link->save();

        return $link;
    }
}
