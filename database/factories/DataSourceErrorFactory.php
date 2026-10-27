<?php

namespace Database\Factories;

use App\Enums\ConnectorErrorCode;
use App\Models\DataSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataSourceErrorFactory extends Factory
{
    private const MESSAGES = [
        'timeout' => 'No response from the remote service after 30 seconds',
        'connection_refused' => 'Connection to the remote host was refused',
        'unauthorized' => 'The remote service rejected the credentials (HTTP 401)',
        'invalid_response' => 'Unexpected response format: expected a list of positions',
        'parsing_failed' => 'Line 214 could not be parsed: unexpected end of record',
    ];

    public function definition(): array
    {
        $code = fake()->randomElement(ConnectorErrorCode::cases());

        return [
            'data_source_id' => DataSource::factory(),
            'occurred_at' => now()->subMinutes(fake()->numberBetween(5, 2880)),
            'code' => $code,
            'message' => self::MESSAGES[$code->value],
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn () => ['resolved_at' => now()->subMinutes(fake()->numberBetween(1, 120))]);
    }
}
