<?php

namespace Tests\Unit;

use App\Support\Tables\PerPage;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class PerPageTest extends TestCase
{
    public function test_an_allowed_page_size_is_used(): void
    {
        $this->assertSame(50, PerPage::from(Request::create('/', 'GET', ['per_page' => '50']), 20));
    }

    public function test_an_unknown_or_missing_page_size_falls_back_to_the_default(): void
    {
        $this->assertSame(25, PerPage::from(Request::create('/', 'GET', ['per_page' => '7']), 25));
        $this->assertSame(25, PerPage::from(Request::create('/', 'GET', ['per_page' => 'all']), 25));
        $this->assertSame(25, PerPage::from(Request::create('/'), 25));
    }
}
