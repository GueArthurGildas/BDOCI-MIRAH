<?php

namespace Database\Factories;

use App\Enums\EezCheckResult;
use App\Enums\EezCheckType;
use App\Models\EezDeclaration;
use Illuminate\Database\Eloquent\Factories\Factory;

class EezDeclarationCheckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'eez_declaration_id' => EezDeclaration::factory(),
            'check_type' => fake()->randomElement(EezCheckType::cases()),
            'result' => EezCheckResult::Ok,
        ];
    }
}
