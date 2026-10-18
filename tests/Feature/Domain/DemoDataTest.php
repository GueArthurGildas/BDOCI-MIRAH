<?php

namespace Tests\Feature\Domain;

use App\Models\Position;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_database_can_be_seeded(): void
    {
        $this->seed();

        $this->assertTrue(User::where('email', 'admin@example.com')->first()->hasRole('admin'));
        $this->assertGreaterThan(0, Vessel::count());
        $this->assertGreaterThan(0, Position::count());
    }
}
