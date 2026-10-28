<?php

namespace App\Support\Connectors;

use App\Enums\ConnectorErrorCode;

final class RecordedPayloads
{
    /** @return array<int, array<string, mixed>> */
    public static function fromFile(string $path): array
    {
        if (! is_file($path)) {
            throw new ConnectorUnavailable(ConnectorErrorCode::ConnectionRefused, "Payload file not found: {$path}");
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            throw new ConnectorUnavailable(ConnectorErrorCode::ParsingFailed, 'The payload file is not valid JSON');
        }

        $records = $decoded['records'] ?? $decoded;

        if (! array_is_list($records)) {
            throw new ConnectorUnavailable(ConnectorErrorCode::InvalidResponse, 'The payload file must contain a list of records');
        }

        return $records;
    }

    /** @return array<int, array<string, mixed>> */
    public static function recorded(string $code): array
    {
        return self::fromFile(rtrim(config('connectors.fixtures_path'), '/')."/{$code}.json");
    }
}
