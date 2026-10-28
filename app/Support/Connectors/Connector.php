<?php

namespace App\Support\Connectors;

use App\Enums\DataSourceMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

interface Connector
{
    public function code(): string;

    /** @return array<int, DataSourceMode> */
    public function supportedModes(): array;

    /** @return iterable<int, array<string, mixed>> */
    public function fetch(DataSourceMode $mode, ?string $file = null): iterable;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws RejectedRecord
     */
    public function normalise(array $payload): array;

    /** @param array<string, mixed> $record */
    public function upsert(array $record, Carbon $readAt): Model;

    /** @param array<string, mixed> $payload */
    public function externalReference(array $payload): ?string;
}
