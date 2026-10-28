<?php

namespace App\Support\Connectors\Sources;

use App\Enums\CaptainSignatureStatus;
use App\Enums\InspectionDocumentType;
use App\Enums\InspectionLocationType;
use App\Enums\MirrorOrigin;
use App\Enums\RejectionReason;
use App\Models\DeclaredObservedCatch;
use App\Models\ExternalInspection;
use App\Models\Gear;
use App\Models\InspectionDocument;
use App\Models\InspectionGear;
use App\Models\InspectionInfraction;
use App\Models\Landing;
use App\Models\Port;
use App\Models\Presentation;
use App\Models\Species;
use App\Models\Transshipment;
use App\Support\Connectors\CatchVariance;
use App\Support\Connectors\RecordedConnector;
use App\Support\Connectors\RejectedRecord;
use App\Support\Connectors\VesselLocator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EInspectionConnector extends RecordedConnector
{
    public function code(): string
    {
        return 'e-inspection';
    }

    public function externalReference(array $payload): ?string
    {
        return $payload['inspection_id'] ?? $payload['reference'] ?? null;
    }

    public function normalise(array $payload): array
    {
        return match ($payload['kind'] ?? 'inspection') {
            'inspection' => $this->normaliseInspection($payload),
            'landing' => $this->normaliseOperation($payload, 'landing', 'landed_at'),
            'transshipment' => $this->normaliseOperation($payload, 'transshipment', 'transshipped_at'),
            default => throw new RejectedRecord(RejectionReason::InvalidValue, 'Unknown record kind "'.$payload['kind'].'"'),
        };
    }

    public function upsert(array $record, Carbon $readAt): Model
    {
        return match ($record['kind']) {
            'inspection' => $this->upsertInspection($record, $readAt),
            'landing' => Landing::updateOrCreate(
                ['vessel_id' => $record['vessel_id'], 'landed_at' => $record['occurred_at'], 'origin' => MirrorOrigin::EInspection->value],
                ['port_id' => $record['port_id'], 'lines' => $record['lines']],
            ),
            'transshipment' => Transshipment::updateOrCreate(
                ['vessel_id' => $record['vessel_id'], 'transshipped_at' => $record['occurred_at'], 'origin' => MirrorOrigin::EInspection->value],
                ['partner_vessel_id' => $record['partner_vessel_id'], 'lines' => $record['lines']],
            ),
        };
    }

    private function normaliseInspection(array $payload): array
    {
        $this->require($payload, 'inspection_id');

        $location = $payload['location'] ?? [];
        $locationType = isset($location['type']) ? InspectionLocationType::tryFrom($location['type']) : null;

        if (isset($location['type']) && $locationType === null) {
            throw new RejectedRecord(RejectionReason::InvalidValue, 'Field "location.type" must be port or sea');
        }

        $signature = isset($payload['captain_signature']) ? CaptainSignatureStatus::tryFrom($payload['captain_signature']) : null;

        if (isset($payload['captain_signature']) && $signature === null) {
            throw new RejectedRecord(RejectionReason::InvalidValue, 'Field "captain_signature" must be signed, refused or absent');
        }

        $catches = collect($payload['catches'] ?? [])->map(fn (array $line) => $this->normaliseCatch($line))->all();

        return [
            'kind' => 'inspection',
            'external_id' => $payload['inspection_id'],
            'reference' => $payload['reference'] ?? null,
            'vessel_id' => VesselLocator::find($payload['vessel'] ?? [])?->id,
            'location_type' => $locationType?->value,
            'port_id' => isset($location['port']) ? Port::where('locality', $location['port'])->orWhere('name', $location['port'])->value('id') : null,
            'latitude' => $location['lat'] ?? null,
            'longitude' => $location['lon'] ?? null,
            'started_at' => isset($payload['started_at']) ? Carbon::parse($payload['started_at']) : null,
            'ended_at' => isset($payload['ended_at']) ? Carbon::parse($payload['ended_at']) : null,
            'control_type' => $payload['control_type'] ?? null,
            'result' => $payload['result'] ?? null,
            'captain_signature_status' => $signature?->value,
            'is_copy_handed_over' => $payload['copy_handed_over'] ?? null,
            'documents' => collect($payload['documents'] ?? [])->map(fn (array $document) => $this->normaliseDocument($document))->all(),
            'gears' => collect($payload['gears'] ?? [])->map(fn (array $gear) => $this->normaliseGear($gear))->all(),
            'catches' => $catches,
            'infractions' => $payload['infractions'] ?? [],
            'raw' => $payload,
        ];
    }

    private function normaliseDocument(array $document): array
    {
        if (InspectionDocumentType::tryFrom($document['type'] ?? '') === null) {
            throw new RejectedRecord(RejectionReason::InvalidValue, 'Field "documents.type" has an unknown value "'.($document['type'] ?? '').'"');
        }

        return ['type' => $document['type'], 'is_compliant' => $document['compliant'] ?? null];
    }

    private function normaliseGear(array $gear): array
    {
        $gearId = null;

        if (filled($gear['gear'] ?? null)) {
            $gearId = Gear::where('code_fao', $gear['gear'])->value('id')
                ?? throw new RejectedRecord(RejectionReason::InvalidValue, "Unknown gear code \"{$gear['gear']}\"");
        }

        return ['gear_id' => $gearId, 'mesh_mm' => $gear['mesh_mm'] ?? null, 'is_marking_compliant' => $gear['marking_compliant'] ?? null, 'is_compliant' => $gear['compliant'] ?? null];
    }

    private function normaliseCatch(array $line): array
    {
        $speciesId = Species::where('fao_code', $line['species'] ?? '')->value('id')
            ?? throw new RejectedRecord(RejectionReason::InvalidValue, 'Unknown species code "'.($line['species'] ?? '').'"');

        $presentationId = null;

        if (filled($line['presentation'] ?? null)) {
            $presentationId = Presentation::where('code', $line['presentation'])->value('id')
                ?? throw new RejectedRecord(RejectionReason::InvalidValue, "Unknown presentation code \"{$line['presentation']}\"");
        }

        $declared = isset($line['declared_kg']) ? (float) $line['declared_kg'] : null;
        $observed = isset($line['observed_kg']) ? (float) $line['observed_kg'] : null;
        $variance = CatchVariance::percent($declared, $observed);

        return [
            'species_id' => $speciesId,
            'presentation_id' => $presentationId,
            'declared_weight_kg' => $declared,
            'observed_weight_kg' => $observed,
            'net_weight_kg' => $line['net_kg'] ?? null,
            'conversion_factor' => $line['conversion_factor'] ?? null,
            'variance_pct' => $variance,
            'exceeds_csp_threshold' => CatchVariance::exceedsThreshold($variance),
        ];
    }

    private function normaliseOperation(array $payload, string $kind, string $dateField): array
    {
        $this->require($payload, $dateField);

        $vessel = VesselLocator::find($payload['vessel'] ?? []);

        if ($vessel === null) {
            throw new RejectedRecord(RejectionReason::UnknownVessel, 'No vessel matches the identifiers of the '.$kind);
        }

        $lines = collect($payload['lines'] ?? [])->map(function (array $line) {
            $species = Species::where('fao_code', $line['species'] ?? '')->first()
                ?? throw new RejectedRecord(RejectionReason::InvalidValue, 'Unknown species code "'.($line['species'] ?? '').'"');

            return ['species_id' => $species->id, 'species' => $species->fao_code, 'weight_kg' => $line['weight_kg'] ?? null];
        })->all();

        return [
            'kind' => $kind,
            'vessel_id' => $vessel->id,
            'partner_vessel_id' => $kind === 'transshipment' ? VesselLocator::find($payload['partner'] ?? [])?->id : null,
            'port_id' => isset($payload['port']) ? Port::where('locality', $payload['port'])->orWhere('name', $payload['port'])->value('id') : null,
            'occurred_at' => Carbon::parse($payload[$dateField]),
            'lines' => $lines,
        ];
    }

    private function upsertInspection(array $record, Carbon $readAt): ExternalInspection
    {
        $inspection = ExternalInspection::updateOrCreate(['external_id' => $record['external_id']], [
            ...collect($record)->only(['reference', 'vessel_id', 'location_type', 'port_id', 'latitude', 'longitude', 'started_at', 'ended_at', 'control_type', 'result', 'captain_signature_status', 'is_copy_handed_over', 'raw'])->all(),
            'read_at' => $readAt,
        ]);

        foreach ($record['documents'] as $document) {
            InspectionDocument::updateOrCreate(['inspection_id' => $inspection->id, 'type' => $document['type']], ['is_compliant' => $document['is_compliant']]);
        }

        foreach ($record['gears'] as $gear) {
            InspectionGear::updateOrCreate(['inspection_id' => $inspection->id, 'gear_id' => $gear['gear_id'], 'mesh_mm' => $gear['mesh_mm']], $gear);
        }

        foreach ($record['catches'] as $line) {
            DeclaredObservedCatch::updateOrCreate(['inspection_id' => $inspection->id, 'species_id' => $line['species_id'], 'presentation_id' => $line['presentation_id']], $line);
        }

        foreach ($record['infractions'] as $infraction) {
            InspectionInfraction::updateOrCreate(['inspection_id' => $inspection->id, 'provision' => $infraction['provision'] ?? null], [
                'stamp' => $infraction['stamp'] ?? null,
                'measures' => $infraction['measures'] ?? null,
            ]);
        }

        return $inspection;
    }
}
