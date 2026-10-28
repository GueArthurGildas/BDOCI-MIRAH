<?php

namespace Database\Factories;

use App\Enums\FnpDeregistrationReason;
use App\Enums\FnpRegistration;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class FnpStatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'status' => FnpRegistration::Registered,
            'status_on' => today()->subYears(2),
        ];
    }

    public function deregistered(FnpDeregistrationReason $reason = FnpDeregistrationReason::FlagChange): static
    {
        return $this->state(['status' => FnpRegistration::Deregistered, 'deregistration_reason' => $reason, 'status_on' => today()->subMonth()]);
    }
}
