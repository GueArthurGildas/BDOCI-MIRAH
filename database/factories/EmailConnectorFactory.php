<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmailConnectorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier' => 'log',
            'domain' => 'csp.example.ci',
            'parameters' => ['mailer' => 'log'],
            'is_active' => true,
        ];
    }
}
