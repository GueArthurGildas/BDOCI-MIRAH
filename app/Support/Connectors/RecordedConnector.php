<?php

namespace App\Support\Connectors;

use App\Enums\ConnectorErrorCode;
use App\Enums\DataSourceMode;
use App\Enums\RejectionReason;

abstract class RecordedConnector implements Connector
{
    public function supportedModes(): array
    {
        return [DataSourceMode::Api, DataSourceMode::DirectRead, DataSourceMode::Import];
    }

    public function fetch(DataSourceMode $mode, ?string $file = null): iterable
    {
        if (! in_array($mode, $this->supportedModes(), true)) {
            throw new ConnectorUnavailable(ConnectorErrorCode::ConnectionRefused, "The {$mode->value} mode is not implemented for {$this->code()}");
        }

        if ($mode === DataSourceMode::Import) {
            return RecordedPayloads::fromFile($file ?? throw new ConnectorUnavailable(ConnectorErrorCode::ConnectionRefused, 'No file given for the controlled import'));
        }

        return RecordedPayloads::recorded($this->code());
    }

    public function externalReference(array $payload): ?string
    {
        return $payload['id'] ?? $payload['inspection_id'] ?? $payload['dpep_id'] ?? null;
    }

    protected function require(array $payload, string ...$fields): void
    {
        foreach ($fields as $field) {
            if (blank(data_get($payload, $field))) {
                throw new RejectedRecord(RejectionReason::MissingField, "Field \"{$field}\" is required");
            }
        }
    }
}
