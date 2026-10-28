<?php

namespace Database\Factories;

use App\Models\ApprovedInstaller;
use App\Models\NewBeaconRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class BeaconInstallationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'new_beacon_request_id' => NewBeaconRequest::factory(),
            'installer_id' => ApprovedInstaller::factory(),
            'installed_on' => today(),
            'report_document_id' => null,
        ];
    }
}
