<?php

namespace Tests\Unit;

use App\Support\Licenses\ExpiryDelay;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class ExpiryDelayTest extends TestCase
{
    private function today(): CarbonImmutable
    {
        return CarbonImmutable::parse('2026-11-04');
    }

    public function test_a_license_expiring_today_says_so(): void
    {
        $delay = ExpiryDelay::describe($this->today(), $this->today());

        $this->assertSame(0, $delay['days']);
        $this->assertFalse($delay['expired']);
        $this->assertSame(__('licenses::licenses.expiry.today'), $delay['label']);
    }

    public function test_a_future_expiry_counts_the_time_left(): void
    {
        $delay = ExpiryDelay::describe($this->today()->addDays(12), $this->today());

        $this->assertSame(12, $delay['days']);
        $this->assertFalse($delay['expired']);
        $this->assertStringContainsString('12', $delay['label']);
        $this->assertSame(__('licenses::licenses.expiry.in', ['interval' => '12 '.($this->daysWord())]), $delay['label']);
    }

    public function test_a_past_expiry_counts_the_time_since(): void
    {
        $delay = ExpiryDelay::describe($this->today()->subDays(12), $this->today());

        $this->assertSame(-12, $delay['days']);
        $this->assertTrue($delay['expired']);
        $this->assertSame(__('licenses::licenses.expiry.since', ['interval' => '12 '.($this->daysWord())]), $delay['label']);
    }

    public function test_long_delays_use_months_and_days_without_weeks(): void
    {
        $label = ExpiryDelay::describe($this->today()->addDays(45), $this->today())['label'];

        $this->assertMatchesRegularExpression('/\b1\b.*\b15\b/u', $label);
        $this->assertDoesNotMatchRegularExpression('/week|semaine/iu', $label);
    }

    private function daysWord(): string
    {
        return app()->getLocale() === 'fr' ? 'jours' : 'days';
    }
}
