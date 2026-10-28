<?php

namespace Modules\Vessels\Tests\Feature;

use App\Enums\SourceSystem;
use App\Models\ExternalInspection;
use App\Models\ExternalLink;
use App\Models\Vessel;
use App\Models\VesselSource;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class ExternalLinkTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC25', partial: true)]
    #[Assumes('H-7-7')]
    public function test_the_source_tab_opens_the_external_system_with_the_identifier(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        VesselSource::factory()->create(['vessel_id' => $vessel->id, 'source' => SourceSystem::Themis, 'external_id' => 'TH-100202']);
        ExternalLink::factory()->create(['source' => 'themis', 'model_url' => 'https://themis.example.org/vessels/{id}']);

        $this->get(route('vessels.show', $vessel))->assertOk()->assertSee('https://themis.example.org/vessels/TH-100202', false);
    }

    #[Covers('UC25', partial: true)]
    #[Assumes('H-7-7')]
    public function test_without_an_address_the_identifier_can_be_copied(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        VesselSource::factory()->create(['vessel_id' => $vessel->id, 'source' => SourceSystem::Themis, 'external_id' => 'TH-100202']);
        ExternalLink::factory()->create(['source' => 'themis', 'model_url' => null, 'is_copy_fallback' => true]);

        $this->get(route('vessels.show', $vessel))->assertOk()->assertSee('clipboard.writeText', false)->assertSee(__('vessels::vessels.sources.copy'));
    }

    #[Covers('UC19', 'UC25', partial: true)]
    public function test_the_inspection_tab_reads_the_mirror(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        ExternalInspection::factory()->create(['vessel_id' => $vessel->id, 'reference' => 'CSP/ABJ/2026/0401']);

        $this->get(route('vessels.show', $vessel))->assertOk()->assertSee('CSP/ABJ/2026/0401');
    }
}
