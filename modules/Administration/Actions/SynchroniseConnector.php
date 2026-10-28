<?php

namespace Modules\Administration\Actions;

use App\Enums\ConnectorErrorCode;
use App\Enums\RejectionStatus;
use App\Models\DataSource;
use App\Models\DataSourceError;
use App\Models\DataSourceRejection;
use App\Models\DataSourceSync;
use App\Models\ExternalInspection;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Connectors\Connector;
use App\Support\Connectors\ConnectorRegistry;
use App\Support\Connectors\ConnectorUnavailable;
use App\Support\Connectors\IndicatorRule;
use App\Support\Connectors\RejectedRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SynchroniseConnector extends Action
{
    public function __construct(private readonly ConnectorRegistry $registry) {}

    public function code(): string
    {
        return 'administration.synchronise_connector';
    }

    public function permission(): Permission
    {
        return Permission::ConnectorsSync;
    }

    public function handle(ActionContext $context, array $input): DataSourceSync
    {
        $source = DataSource::findOrFail($input['data_source_id']);
        $connector = $this->registry->get($source->code);
        $startedAt = now();
        $sync = DataSourceSync::create(['data_source_id' => $source->id, 'started_at' => $startedAt]);
        $received = 0;
        $rejected = 0;

        try {
            foreach ($connector->fetch($source->mode, $input['file'] ?? null) as $payload) {
                $received++;

                if (! $this->ingest($context, $connector, $source, $sync, $payload, $startedAt)) {
                    $rejected++;
                }
            }
        } catch (ConnectorUnavailable $failure) {
            return $this->fail($source, $sync, $failure, $received, $rejected);
        }

        $sync->update(['finished_at' => now(), 'received_count' => $received, 'rejected_count' => $rejected]);
        $source->update([
            'last_synced_at' => $startedAt,
            'is_stale' => false,
            'last_error' => null,
            'indicator_status' => IndicatorRule::evaluate(false, $rejected, false),
        ]);

        return $sync;
    }

    private function ingest(ActionContext $context, Connector $connector, DataSource $source, DataSourceSync $sync, array $payload, $readAt): bool
    {
        $reference = $connector->externalReference($payload) ?? 'sha1:'.substr(sha1(json_encode($payload)), 0, 16);

        try {
            $record = $connector->normalise($payload);
            $model = DB::transaction(fn () => $connector->upsert($record, $readAt));
        } catch (RejectedRecord $rejection) {
            $this->reject($source, $sync, $reference, $payload, $rejection);

            return false;
        }

        $this->settlePreviousRejections($context, $source, $reference);
        $this->publish($context, $model);

        return true;
    }

    private function reject(DataSource $source, DataSourceSync $sync, ?string $reference, array $payload, RejectedRecord $rejection): void
    {
        $existing = $reference === null ? null : DataSourceRejection::where('data_source_id', $source->id)
            ->where('external_ref', $reference)
            ->where('status', RejectionStatus::Pending)
            ->first();

        $attributes = [
            'data_source_sync_id' => $sync->id,
            'reason' => $rejection->reason,
            'details' => $rejection->details,
            'payload' => $payload,
            'received_at' => $sync->started_at,
        ];

        if ($existing !== null) {
            $existing->update($attributes);

            return;
        }

        DataSourceRejection::create([...$attributes, 'data_source_id' => $source->id, 'external_ref' => $reference, 'status' => RejectionStatus::Pending]);
    }

    private function settlePreviousRejections(ActionContext $context, DataSource $source, ?string $reference): void
    {
        if ($reference === null) {
            return;
        }

        DataSourceRejection::where('data_source_id', $source->id)
            ->where('external_ref', $reference)
            ->where('status', RejectionStatus::Pending)
            ->get()
            ->each->update(['status' => RejectionStatus::Corrected, 'handled_at' => now(), 'handled_by' => $context->actor->id]);
    }

    private function publish(ActionContext $context, Model $model): void
    {
        if ($model instanceof ExternalInspection && $model->wasRecentlyCreated && $model->vessel_id !== null) {
            $context->publish(
                vessel: $model->vessel_id,
                module: 'inspections',
                type: 'inspection.received',
                subject: $model,
                summaryKey: 'inspections::events.received',
                params: ['reference' => $model->reference ?? $model->external_id],
                source: 'e_inspection',
                occurredAt: $model->started_at,
            );
        }
    }

    private function fail(DataSource $source, DataSourceSync $sync, ConnectorUnavailable $failure, int $received, int $rejected): DataSourceSync
    {
        $sync->update(['received_count' => $received, 'rejected_count' => $rejected, 'message' => $failure->getMessage()]);

        DataSourceError::create([
            'data_source_id' => $source->id,
            'data_source_sync_id' => $sync->id,
            'occurred_at' => now(),
            'code' => $failure->errorCode ?? ConnectorErrorCode::InvalidResponse,
            'message' => $failure->getMessage(),
        ]);

        $source->update([
            'last_error' => $failure->getMessage(),
            'indicator_status' => IndicatorRule::evaluate(true, $rejected, $source->is_stale),
        ]);

        return $sync;
    }
}
