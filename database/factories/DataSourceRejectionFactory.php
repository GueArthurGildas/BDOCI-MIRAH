<?php

namespace Database\Factories;

use App\Enums\RejectionReason;
use App\Enums\RejectionStatus;
use App\Models\DataSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataSourceRejectionFactory extends Factory
{
    public function definition(): array
    {
        $reason = fake()->randomElement(RejectionReason::cases());
        $mmsi = (string) fake()->numberBetween(600000000, 699999999);

        return [
            'data_source_id' => DataSource::factory(),
            'external_ref' => 'REC-'.fake()->unique()->numberBetween(10000, 99999),
            'reason' => $reason,
            'details' => match ($reason) {
                RejectionReason::MissingField => 'Field "latitude" is required',
                RejectionReason::InvalidValue => 'Field "speed_knots" must be between 0 and 40',
                RejectionReason::DuplicateRecord => 'A record with the same MMSI and timestamp already exists',
                RejectionReason::UnknownVessel => "No vessel matches MMSI {$mmsi}",
                RejectionReason::OutOfZone => 'The position is outside the authorized zones',
            },
            'payload' => [
                'mmsi' => $mmsi,
                'recorded_at' => now()->subHours(fake()->numberBetween(1, 48))->toIso8601String(),
                'latitude' => $reason === RejectionReason::MissingField ? null : fake()->randomFloat(5, 3.5, 5.0),
                'longitude' => fake()->randomFloat(5, -7.4, -3.0),
                'speed_knots' => $reason === RejectionReason::InvalidValue ? 128.4 : fake()->randomFloat(1, 0, 12),
            ],
            'status' => RejectionStatus::Pending,
            'received_at' => now()->subMinutes(fake()->numberBetween(5, 2880)),
        ];
    }

    public function handled(RejectionStatus $status): static
    {
        return $this->state(fn () => ['status' => $status, 'handled_at' => now()->subMinutes(fake()->numberBetween(1, 120))]);
    }
}
