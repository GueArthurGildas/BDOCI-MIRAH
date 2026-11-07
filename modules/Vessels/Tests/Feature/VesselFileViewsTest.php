<?php

namespace Modules\Vessels\Tests\Feature;

use App\Models\License;
use App\Models\Movement;
use App\Models\Port;
use App\Models\Position;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VesselFileViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_folder_view_shows_nested_folders_with_counts_and_items(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        $license = License::factory()->for($vessel)->create(['number' => 'LIC-FOLDER-1']);
        $device = TrackingDevice::factory()->for($vessel)->create(['identifier' => 'VMS-FOLDER-9']);

        $this->get(route('vessels.show', [$vessel, 'view' => 'folders', 'pane' => 'licenses']))
            ->assertOk()
            ->assertSee('gs-tree', false)
            ->assertSee(__('vessels::vessels.folders.licensing'))
            ->assertSee('LIC-FOLDER-1')
            ->assertSee('#item-licenses-'.$license->id, false)
            ->assertSee('VMS-FOLDER-9')
            ->assertSee('item-devices-'.$device->id, false)
            ->assertSee('id="item-licenses-'.$license->id.'"', false)
            ->assertViewHas('pane', 'licenses');
    }

    public function test_the_folder_view_falls_back_to_the_identity_pane(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.show', [$vessel, 'view' => 'folders', 'pane' => 'nonsense']))->assertOk()->assertViewHas('pane', 'identity');
        $this->get(route('vessels.show', [$vessel, 'view' => 'folders']))->assertViewHas('pane', 'identity');
        $this->get(route('vessels.show', [$vessel, 'view' => 'cards']))->assertSessionHasErrors('view');
    }

    public function test_the_folder_view_hides_panes_the_user_cannot_open(): void
    {
        $this->signInWithPermissions(Permission::VesselsView);
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.show', [$vessel, 'view' => 'folders', 'pane' => 'infractions']))
            ->assertOk()
            ->assertViewHas('pane', 'identity')
            ->assertViewHas('tree', function (array $tree) {
                $keys = collect($tree)->pluck('leaves')->flatten(1)->pluck('key');

                return $keys->contains('identity') && $keys->contains('photos') && $keys->doesntContain('infractions') && $keys->doesntContain('licenses');
            });
    }

    public function test_the_tab_view_stays_the_default(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.show', $vessel))->assertOk()->assertViewHas('view', 'tabs')->assertDontSee('data-gs-tree-root', false);
    }

    public function test_the_summary_shows_the_last_emission_with_its_gap(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        Position::factory()->for($vessel)->create(['recorded_at' => now()->subHours(30)]);

        $this->get(route('vessels.show', $vessel))
            ->assertSee(__('vessels::vessels.fields.last_emission'))
            ->assertSee(__('ui.emission_gap.days_hours', ['days' => 1, 'hours' => 6]));
    }

    public function test_a_vessel_that_never_emitted_says_so(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.show', $vessel))->assertSee(__('vessels::vessels.summary.never_emitted'));
    }

    public function test_statuses_explain_themselves_on_hover(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.show', $vessel))
            ->assertSee('data-bs-toggle="tooltip"', false)
            ->assertSee(__('enums.descriptions.vessel_status.active'));
    }

    public function test_movements_use_entry_and_exit_arrows(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        Movement::factory()->for($vessel)->for(Port::factory())->create(['direction' => 'entry']);
        Movement::factory()->for($vessel)->for(Port::factory())->create(['direction' => 'exit']);

        $this->get(route('vessels.show', $vessel))
            ->assertSee('icon-arrow-down-left', false)
            ->assertSee('icon-arrow-up-right', false);
    }

    public function test_the_technical_sheet_downloads_as_a_pdf(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create(['name' => 'AKWABA FICHE', 'registration_number' => 'CI-FICHE-1']);

        $response = $this->get(route('vessels.sheet', $vessel));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('fiche-ci-fiche-1.pdf', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_the_technical_sheet_embeds_the_primary_photo(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        $this->post(route('vessels.photos.store', $vessel), ['photos' => [UploadedFile::fake()->image('a.jpg', 300, 200)]]);

        $this->get(route('vessels.sheet', $vessel))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_the_technical_sheet_needs_the_view_permission(): void
    {
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.sheet', $vessel))->assertRedirect(route('login'));
    }
}
