<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_id' => fn () => Document::create(['name' => fake()->bothify('licence-####.pdf'), 'mime_type' => 'application/pdf', 'path' => fake()->bothify('documents/licence-####.pdf')])->id,
            'license_id' => License::factory(),
        ];
    }
}
