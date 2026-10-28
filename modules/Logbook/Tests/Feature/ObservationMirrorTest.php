<?php

namespace Modules\Logbook\Tests\Feature;

use App\Enums\ObservationStatus;
use App\Enums\SurveillanceObservationStatus;
use App\Models\Observation;
use App\Models\SurveillanceObservation;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class ObservationMirrorTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('RG08', 'UC08', partial: true)]
    #[Assumes('H-3a-6')]
    public function test_legacy_observations_are_kept_on_the_vessel_in_the_surveillance_observations(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post('/logbook', [
            'observed_at' => now()->subHour()->format('Y-m-d H:i'),
            'vessel_id' => $vessel->id,
            'level' => 'warning',
            'description' => 'Rupture d\'émission constatée.',
        ])->assertRedirect();

        $mirror = SurveillanceObservation::sole();
        $this->assertSame($vessel->id, $mirror->vessel_id);
        $this->assertSame('Rupture d\'émission constatée.', $mirror->body);
        $this->assertSame($agent->id, $mirror->created_by);
        $this->assertSame(SurveillanceObservationStatus::Monitoring, $mirror->status);
        $this->assertSame(Observation::sole()->id, $mirror->legacy_observation_id);
    }

    #[Covers('RG08', partial: true)]
    #[Assumes('H-3a-6')]
    public function test_closing_a_legacy_observation_closes_its_mirror(): void
    {
        $this->signInAs(Role::Agent);
        $observation = Observation::factory()->create(['status' => ObservationStatus::Open]);
        $this->patch(route('logbook.update', $observation), ['status' => 'closed', 'follow_up' => 'Régularisé.']);

        $this->assertSame(SurveillanceObservationStatus::Closed, SurveillanceObservation::sole()->status);
    }
}
