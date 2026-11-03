<?php

namespace Modules\Vessels\Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VesselPhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(DocumentStore::DISK);
    }

    private function upload(Vessel $vessel, int $count = 1): void
    {
        $files = collect(range(1, $count))->map(fn (int $index) => UploadedFile::fake()->image("photo-{$index}.jpg", 400, 300))->all();

        $this->post(route('vessels.photos.store', $vessel), ['photos' => $files])->assertRedirect(route('vessels.show', $vessel));
    }

    private function manager(): User
    {
        return $this->signInWithPermissions(Permission::VesselsView, Permission::VesselsManage);
    }

    public function test_photos_are_added_linked_to_the_vessel_and_the_first_becomes_primary(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();

        $this->upload($vessel, 2);

        $vessel->refresh();
        $documents = app(DocumentStore::class)->for($vessel);
        $this->assertCount(2, $documents);
        $this->assertSame($documents->last()->id, $vessel->primary_photo_id);
        $this->assertDatabaseHas('vessel_events', ['vessel_id' => $vessel->id, 'type' => 'vessel.photo_added']);
    }

    public function test_adding_a_photo_keeps_the_existing_primary_photo(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();
        $this->upload($vessel);
        $primary = $vessel->fresh()->primary_photo_id;

        $this->upload($vessel);

        $this->assertSame($primary, $vessel->fresh()->primary_photo_id);
    }

    public function test_removing_the_primary_photo_promotes_the_next_one_then_clears_it(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();
        $this->upload($vessel, 2);
        [$newest, $oldest] = app(DocumentStore::class)->for($vessel)->all();

        $this->delete(route('vessels.photos.destroy', [$vessel, $oldest]))->assertRedirect();
        $this->assertSame($newest->id, $vessel->fresh()->primary_photo_id);

        $this->delete(route('vessels.photos.destroy', [$vessel, $newest]))->assertRedirect();
        $this->assertNull($vessel->fresh()->primary_photo_id);
        $this->assertDatabaseHas('documents', ['id' => $oldest->id]);
        $this->assertDatabaseHas('vessel_events', ['vessel_id' => $vessel->id, 'type' => 'vessel.photo_removed']);
    }

    public function test_a_photo_is_only_served_through_its_own_vessel(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();
        $other = Vessel::factory()->create();
        $this->upload($vessel);
        $photo = app(DocumentStore::class)->for($vessel)->first();

        $this->get(route('vessels.photos.show', [$vessel, $photo]))->assertOk()->assertHeader('Cache-Control');
        $this->get(route('vessels.photos.show', [$other, $photo]))->assertNotFound();
    }

    public function test_non_image_documents_are_not_served_as_photos(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();
        $document = app(DocumentStore::class)->storeContent('notes.txt', 'text', 'text/plain');
        app(DocumentStore::class)->attach($document, $vessel);

        $this->get(route('vessels.photos.show', [$vessel, $document]))->assertNotFound();
    }

    public function test_only_images_are_accepted(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();

        $this->post(route('vessels.photos.store', $vessel), ['photos' => [UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf')]])
            ->assertSessionHasErrors('photos.0');

        $this->assertSame(0, Document::count());
    }

    public function test_viewers_can_see_photos_but_not_change_them(): void
    {
        $this->signInWithPermissions(Permission::VesselsView);
        $vessel = Vessel::factory()->create();

        $this->post(route('vessels.photos.store', $vessel), ['photos' => [UploadedFile::fake()->image('a.jpg')]])->assertForbidden();
        $this->get(route('vessels.show', $vessel))->assertOk()->assertDontSee('name="photos[]"', false);
    }

    public function test_the_carousel_and_the_thumbnail_use_the_primary_photo(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();
        $this->upload($vessel, 2);
        $primary = $vessel->fresh()->primary_photo_id;

        $this->get(route('vessels.show', $vessel))
            ->assertOk()
            ->assertSee('data-gs-carousel', false)
            ->assertSee(route('vessels.photos.show', [$vessel, $primary]), false);

        $this->get(route('vessels.index'))->assertSee('class="gs-vessel-thumb"', false)->assertSee(route('vessels.photos.show', [$vessel, $primary]), false);
    }

    public function test_vessels_without_photo_show_the_shared_placeholder(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();

        $this->assertSame(asset('images/vessel-placeholder.svg'), $vessel->photoUrl());
        $this->get(route('vessels.index'))->assertSee('vessel-placeholder.svg');
        $this->assertFileExists(public_path('images/vessel-placeholder.svg'));
    }

    public function test_photo_actions_are_forbidden_in_the_database_when_the_link_is_missing(): void
    {
        $this->manager();
        $vessel = Vessel::factory()->create();
        $this->upload($vessel);
        $photo = app(DocumentStore::class)->for($vessel)->first();
        DB::table('document_links')->delete();

        $this->delete(route('vessels.photos.destroy', [$vessel, $photo]))->assertNotFound();
    }
}
