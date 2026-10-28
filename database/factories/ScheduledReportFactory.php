<?php

namespace Database\Factories;

use App\Enums\ReportFormat;
use App\Enums\ReportFrequency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'operations_activity',
            'frequency' => ReportFrequency::Monthly,
            'format' => ReportFormat::Pdf,
            'recipient_id' => User::factory(),
            'is_active' => true,
        ];
    }
}
