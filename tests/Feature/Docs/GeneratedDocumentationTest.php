<?php

namespace Tests\Feature\Docs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GeneratedDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_reference_documentation_is_up_to_date(): void
    {
        $this->assertSame(0, Artisan::call('docs:generate', ['--check' => true]), Artisan::output());
    }

    public function test_the_acceptance_report_is_up_to_date_and_cites_only_known_identifiers(): void
    {
        $this->assertSame(0, Artisan::call('docs:acceptance', ['--check' => true]), Artisan::output());
    }
}
