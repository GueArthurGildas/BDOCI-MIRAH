<?php

namespace Database\Factories;

use App\Models\EezDeclaration;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntryExitLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'entry_declaration_id' => EezDeclaration::factory(),
            'exit_declaration_id' => EezDeclaration::factory()->exit(),
            'variance_kg' => 0,
        ];
    }
}
