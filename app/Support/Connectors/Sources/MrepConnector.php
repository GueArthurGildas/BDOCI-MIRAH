<?php

namespace App\Support\Connectors\Sources;

use App\Enums\EntryDecision;
use App\Enums\MrepPurpose;
use App\Enums\RejectionReason;
use App\Models\ExternalInspection;
use App\Models\MrepDpep;
use App\Models\MrepEntryDecision;
use App\Models\MrepGiesRefusal;
use App\Models\MrepNotification;
use App\Models\MrepPortCall;
use App\Models\MrepTargeting;
use App\Models\Port;
use App\Support\Connectors\RecordedConnector;
use App\Support\Connectors\RejectedRecord;
use App\Support\Connectors\VesselLocator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MrepConnector extends RecordedConnector
{
    private const DEADLINE_HOURS = 72;

    public function code(): string
    {
        return 'dpep-mrep';
    }

    public function externalReference(array $payload): ?string
    {
        return $payload['dpep_id'] ?? $payload['reference'] ?? null;
    }

    public function normalise(array $payload): array
    {
        return match ($payload['kind'] ?? 'dpep') {
            'dpep' => $this->normaliseDpep($payload),
            'gies_refusal' => $this->normaliseRefusal($payload),
            'targeting' => $this->normaliseTargeting($payload),
            default => throw new RejectedRecord(RejectionReason::InvalidValue, 'Unknown record kind "'.$payload['kind'].'"'),
        };
    }

    public function upsert(array $record, Carbon $readAt): Model
    {
        return match ($record['kind']) {
            'dpep' => $this->upsertDpep($record, $readAt),
            'gies_refusal' => MrepGiesRefusal::updateOrCreate(['vessel_id' => $record['vessel_id'], 'reference' => $record['reference']], ['refused_on' => $record['refused_on']]),
            'targeting' => MrepTargeting::updateOrCreate(['year' => $record['year'], 'target' => $record['target']], ['objective_pct' => $record['objective_pct'], 'achieved_pct' => $record['achieved_pct']]),
        };
    }

    private function normaliseDpep(array $payload): array
    {
        $this->require($payload, 'dpep_id');

        $purpose = isset($payload['purpose']) ? MrepPurpose::tryFrom(strtolower($payload['purpose'])) : null;

        if (isset($payload['purpose']) && $purpose === null) {
            throw new RejectedRecord(RejectionReason::InvalidValue, "Field \"purpose\" must be one of lan, trx, pro, other, got \"{$payload['purpose']}\"");
        }

        $decision = $payload['decision'] ?? null;

        if ($decision !== null && EntryDecision::tryFrom($decision['type'] ?? '') === null) {
            throw new RejectedRecord(RejectionReason::InvalidValue, 'Field "decision.type" must be authorization, refusal or inspection_only');
        }

        $arrival = isset($payload['eta']) ? Carbon::parse($payload['eta']) : null;

        return [
            'kind' => 'dpep',
            'external_id' => $payload['dpep_id'],
            'vessel_id' => VesselLocator::find($payload['vessel'] ?? [])?->id,
            'number' => $payload['number'] ?? null,
            'port_id' => isset($payload['port']) ? Port::where('locality', $payload['port'])->orWhere('name', $payload['port'])->value('id') : null,
            'planned_arrival_at' => $arrival,
            'purpose' => $purpose?->value,
            'last_port_call' => $payload['last_port_call'] ?? null,
            'catches_on_board' => $payload['catches_on_board'] ?? null,
            'catches_to_land' => $payload['catches_to_land'] ?? null,
            'is_complete' => $payload['complete'] ?? null,
            'is_72h_deadline_met' => $payload['deadline_met'] ?? $this->deadlineMet($payload, $arrival),
            'decision' => $decision,
            'port_calls' => $payload['port_calls'] ?? [],
            'notifications' => $payload['notifications'] ?? [],
            'raw' => $payload,
        ];
    }

    private function deadlineMet(array $payload, ?Carbon $arrival): ?bool
    {
        if ($arrival === null || empty($payload['submitted_at'])) {
            return null;
        }

        return Carbon::parse($payload['submitted_at'])->diffInHours($arrival, false) >= self::DEADLINE_HOURS;
    }

    private function normaliseRefusal(array $payload): array
    {
        $this->require($payload, 'reference');

        $vessel = VesselLocator::find($payload['vessel'] ?? []);

        if ($vessel === null) {
            throw new RejectedRecord(RejectionReason::UnknownVessel, 'No vessel matches the identifiers of the GIES refusal '.$payload['reference']);
        }

        return ['kind' => 'gies_refusal', 'vessel_id' => $vessel->id, 'reference' => $payload['reference'], 'refused_on' => $payload['refused_on'] ?? null];
    }

    private function normaliseTargeting(array $payload): array
    {
        $this->require($payload, 'year', 'target', 'objective_pct');

        if (! is_numeric($payload['objective_pct']) || $payload['objective_pct'] < 0 || $payload['objective_pct'] > 100) {
            throw new RejectedRecord(RejectionReason::InvalidValue, 'Field "objective_pct" must be between 0 and 100');
        }

        return [
            'kind' => 'targeting',
            'year' => (int) $payload['year'],
            'target' => $payload['target'],
            'objective_pct' => $payload['objective_pct'],
            'achieved_pct' => $payload['achieved_pct'] ?? null,
        ];
    }

    private function upsertDpep(array $record, Carbon $readAt): MrepDpep
    {
        $dpep = MrepDpep::updateOrCreate(['external_id' => $record['external_id']], [
            ...collect($record)->only(['vessel_id', 'number', 'port_id', 'planned_arrival_at', 'purpose', 'last_port_call', 'catches_on_board', 'catches_to_land', 'is_complete', 'is_72h_deadline_met', 'raw'])->all(),
            'read_at' => $readAt,
        ]);

        if ($record['decision'] !== null) {
            MrepEntryDecision::updateOrCreate(
                ['dpep_id' => $dpep->id, 'decided_at' => Carbon::parse($record['decision']['at'])],
                ['decision' => $record['decision']['type'], 'decision_maker' => $record['decision']['by'] ?? null],
            );
        }

        foreach ($record['port_calls'] as $call) {
            MrepPortCall::updateOrCreate(['dpep_id' => $dpep->id, 'arrival' => Carbon::parse($call['arrival'])], [
                'departure' => isset($call['departure']) ? Carbon::parse($call['departure']) : null,
                'is_inspected' => $call['inspected'] ?? null,
                'is_infraction_recorded' => $call['infraction'] ?? null,
                'inspection_id' => isset($call['inspection_id']) ? ExternalInspection::where('external_id', $call['inspection_id'])->value('id') : null,
            ]);
        }

        foreach ($record['notifications'] as $notification) {
            MrepNotification::updateOrCreate(
                ['dpep_id' => $dpep->id, 'recipient' => $notification['recipient'], 'channel' => $notification['channel'] ?? null],
                ['notified_at' => isset($notification['at']) ? Carbon::parse($notification['at']) : null, 'status' => $notification['status'] ?? null],
            );
        }

        return $dpep;
    }
}
