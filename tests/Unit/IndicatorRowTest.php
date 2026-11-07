<?php

namespace Tests\Unit;

use App\Models\Vessel;
use App\Support\Indicators\IndicatorFilters;
use App\Support\Indicators\IndicatorRow;
use PHPUnit\Framework\TestCase;

class IndicatorRowTest extends TestCase
{
    public function test_a_detail_given_as_a_closure_is_only_computed_when_read(): void
    {
        $calls = 0;
        $row = new IndicatorRow(new Vessel, null, function () use (&$calls) {
            $calls++;

            return 'reasons';
        });

        $this->assertSame(0, $calls);
        $this->assertSame('reasons', $row->detail());
        $this->assertSame(1, $calls);
    }

    public function test_a_plain_detail_is_returned_as_is(): void
    {
        $this->assertSame('text', (new IndicatorRow(new Vessel, null, 'text'))->detail());
        $this->assertNull((new IndicatorRow(new Vessel, null))->detail());
    }

    public function test_filters_only_need_the_vessels_when_one_of_them_narrows_the_list(): void
    {
        $this->assertFalse((new IndicatorFilters)->needsVessels());
        $this->assertTrue((new IndicatorFilters(search: 'a'))->needsVessels());
        $this->assertTrue((new IndicatorFilters(zoneId: 3))->needsVessels());
        $this->assertFalse((new IndicatorFilters(flag: null))->needsVessels());
    }
}
