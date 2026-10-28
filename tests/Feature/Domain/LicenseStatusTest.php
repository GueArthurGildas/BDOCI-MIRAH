<?php

namespace Tests\Feature\Domain;

use App\Enums\LicenseStatus;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class LicenseStatusTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('RG07', 'UC03', partial: true)]
    public function test_status_is_derived_from_validity_dates_and_suspension(): void
    {
        $this->assertSame(LicenseStatus::Valid, License::factory()->expiringIn(120)->create()->status);
        $this->assertSame(LicenseStatus::Expiring, License::factory()->expiringIn(10)->create()->status);
        $this->assertSame(LicenseStatus::Expired, License::factory()->expired()->create()->status);
        $this->assertSame(LicenseStatus::Suspended, License::factory()->create(['suspended_at' => now()])->status);
    }

    #[Covers('RG01', partial: true)]
    public function test_licenses_can_be_filtered_by_status(): void
    {
        $valid = License::factory()->expiringIn(120)->create();
        $expiring = License::factory()->expiringIn(10)->create();
        $expired = License::factory()->expired()->create();

        $this->assertEquals([$valid->id], License::withStatus(LicenseStatus::Valid)->pluck('id')->all());
        $this->assertEquals([$expiring->id], License::withStatus(LicenseStatus::Expiring)->pluck('id')->all());
        $this->assertEquals([$expired->id], License::withStatus(LicenseStatus::Expired)->pluck('id')->all());
    }
}
