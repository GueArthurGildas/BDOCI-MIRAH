<?php

namespace Database\Factories;

use App\Enums\NoteType;
use App\Enums\NoteUrgency;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class InternalNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => NoteType::ToCheck,
            'vessel_id' => Vessel::factory(),
            'urgency' => NoteUrgency::Normal,
            'recipient_user_id' => User::factory(),
            'author_id' => User::factory(),
            'free_text' => fake()->sentence(),
            'is_read' => false,
        ];
    }
}
