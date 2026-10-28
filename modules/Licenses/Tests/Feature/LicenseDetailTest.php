<?php

namespace Modules\Licenses\Tests\Feature;

use App\Models\Gear;
use App\Models\License;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\Zone;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC02', 'RG07', partial: true)]
class LicenseDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_license_is_recorded_with_zones_gears_species_and_conditions(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $zone = Zone::factory()->create();
        $gear = Gear::factory()->create();
        $species = Species::factory()->create();

        $this->post(route('licenses.store'), [
            'vessel_id' => $vessel->id,
            'number' => 'LIC-DETAIL-1',
            'segment' => 'semi_industrial',
            'issued_on' => today()->toDateString(),
            'expires_on' => today()->addYear()->toDateString(),
            'zone_ids' => [$zone->id],
            'gear_ids' => [$gear->id],
            'species_ids' => [$species->id],
            'conditions' => "Observer on board\nNo fishing within 12 miles",
        ])->assertRedirect();

        $license = License::sole();
        $this->assertSame([$zone->id], $license->zones->pluck('id')->all());
        $this->assertSame([$gear->id], $license->gears->pluck('id')->all());
        $this->assertSame([$species->id], $license->species->pluck('id')->all());
        $this->assertSame(['Observer on board', 'No fishing within 12 miles'], $license->conditions->pluck('body')->all());
        $this->assertFalse($vessel->fresh()->license_not_provided);

        $this->get(route('licenses.show', $license))->assertOk()->assertSee('LIC-DETAIL-1')->assertSee('Observer on board')->assertSee($zone->name);
    }

    public function test_a_license_is_updated_through_an_action_and_published_to_the_journal(): void
    {
        $this->signInAs(Role::Director);
        $license = License::factory()->create(['number' => 'LIC-OLD']);

        $this->put(route('licenses.update', $license), [
            'number' => 'LIC-NEW',
            'segment' => 'artisanal',
            'issued_on' => today()->subMonth()->toDateString(),
            'expires_on' => today()->addMonths(6)->toDateString(),
            'conditions' => 'Only daylight fishing',
        ])->assertRedirect(route('licenses.show', $license));

        $this->assertSame('LIC-NEW', $license->fresh()->number);
        $this->assertSame(['Only daylight fishing'], $license->fresh()->conditions->pluck('body')->all());
        $this->assertSame('license.updated', VesselEvent::sole()->type);
    }

    public function test_agents_can_read_but_not_edit_a_license(): void
    {
        $this->signInAs(Role::Agent);
        $license = License::factory()->create();

        $this->get(route('licenses.show', $license))->assertOk()->assertDontSee(route('licenses.edit', $license));
        $this->get(route('licenses.edit', $license))->assertForbidden();
        $this->put(route('licenses.update', $license), [])->assertForbidden();
    }

    public function test_a_pdf_can_be_attached_to_a_license_and_to_other_vessels(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        $license = License::factory()->create();
        $other = Vessel::factory()->create();

        $this->post(route('licenses.documents.store', $license), [
            'file' => UploadedFile::fake()->create('licence.pdf', 20, 'application/pdf'),
            'vessel_ids' => [$other->id],
        ])->assertRedirect(route('licenses.show', $license));

        $this->assertDatabaseCount('license_documents', 2);
        $this->assertDatabaseHas('license_documents', ['vessel_id' => $other->id, 'license_id' => null]);
        $this->assertSame(2, VesselEvent::where('type', 'license.document_attached')->count());
        $this->get(route('licenses.show', $license))->assertSee('licence.pdf');
    }

    public function test_only_pdf_files_are_accepted(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        $license = License::factory()->create();

        $this->post(route('licenses.documents.store', $license), ['file' => UploadedFile::fake()->create('notes.txt', 2, 'text/plain')])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('license_documents', 0);
    }
}
