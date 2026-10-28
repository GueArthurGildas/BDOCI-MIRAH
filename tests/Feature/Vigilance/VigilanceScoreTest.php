<?php

namespace Tests\Feature\Vigilance;

use App\Enums\InfractionStatus;
use App\Models\Infraction;
use App\Models\License;
use App\Models\Vessel;
use App\Support\Vessels\VesselIndicators;
use App\Support\Vessels\VigilanceScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Assumes('H-8-2')]
class VigilanceScoreTest extends TestCase
{
    use RefreshDatabase;

    private function score(Vessel $vessel): array
    {
        return VigilanceScore::compute(VesselIndicators::for($vessel->fresh()));
    }

    private function points(array $score, string $key): int
    {
        return collect($score['components'])->firstWhere('key', $key)['points'];
    }

    #[Covers('UC25', partial: true)]
    public function test_a_clean_vessel_scores_zero_with_every_component_listed(): void
    {
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expiringIn(200)->create();

        $score = $this->score($vessel);

        $this->assertSame(0, $score['total']);
        $this->assertSame('low', $score['level']);
        $this->assertSame(array_keys(config('vigilance.score.components')), array_column($score['components'], 'key'));
    }

    #[Covers('UC25', partial: true)]
    public function test_the_score_is_the_sum_of_readable_weighted_components(): void
    {
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expired()->create();
        Infraction::factory()->count(2)->create(['vessel_id' => $vessel->id, 'status' => InfractionStatus::Reported, 'detected_at' => now()->subDays(10)]);

        $score = $this->score($vessel);

        $this->assertSame(20, $this->points($score, 'license_blocked'));
        $this->assertSame(30, $this->points($score, 'open_infractions'));
        $this->assertSame(10, $this->points($score, 'infractions_12_months'));
        $this->assertSame(array_sum(array_column($score['components'], 'points')), $score['total']);
        $this->assertSame('high', $score['level']);
    }

    #[Covers('UC25', partial: true)]
    public function test_a_component_never_exceeds_its_cap_and_the_total_is_capped(): void
    {
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expired()->create();
        Infraction::factory()->count(10)->create(['vessel_id' => $vessel->id, 'status' => InfractionStatus::Reported, 'detected_at' => now()->subDays(5)]);

        $score = $this->score($vessel);

        $this->assertSame(config('vigilance.score.components.open_infractions.cap'), $this->points($score, 'open_infractions'));
        $this->assertLessThanOrEqual(config('vigilance.score.maximum'), $score['total']);
    }

    public function test_levels_follow_the_configured_thresholds(): void
    {
        $this->assertSame('low', VigilanceScore::level(24));
        $this->assertSame('moderate', VigilanceScore::level(25));
        $this->assertSame('high', VigilanceScore::level(50));
    }
}
