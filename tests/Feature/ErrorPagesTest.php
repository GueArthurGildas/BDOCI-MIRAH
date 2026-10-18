<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_found_page_uses_the_application_design(): void
    {
        $this->withHeader('Accept-Language', 'fr')
            ->get('/missing-page')
            ->assertNotFound()
            ->assertSee('Page introuvable')
            ->assertSee(__('ui.back_home', locale: 'fr'));
    }

    public function test_forbidden_page_is_translated(): void
    {
        $this->actingAs(User::factory()->create())
            ->withHeader('Accept-Language', 'en')
            ->get('/dashboard')
            ->assertForbidden()
            ->assertSee('Access denied');
    }
}
