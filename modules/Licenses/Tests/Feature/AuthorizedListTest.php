<?php

namespace Modules\Licenses\Tests\Feature;

use App\Models\AuthorizedListPublication;
use App\Models\License;
use App\Models\LicenseRegister;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use App\Support\Licenses\AuthorizedList;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('RG01', 'UC03', partial: true)]
class AuthorizedListTest extends TestCase
{
    use RefreshDatabase;

    #[Assumes('H-4a-4')]
    public function test_only_active_unexpired_licenses_authorize_a_vessel(): void
    {
        $active = License::factory()->expiringIn(200)->create();
        License::factory()->expired()->create();
        License::factory()->suspended()->expiringIn(200)->create();
        License::factory()->revoked()->expiringIn(200)->create();

        $rows = app(AuthorizedList::class)->rows();

        $this->assertSame([$active->id], $rows->pluck('id')->all());
    }

    #[Assumes('H-4a-1', 'H-4a-4')]
    public function test_a_vessel_with_several_licenses_appears_once_with_the_latest_end_date(): void
    {
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expiringIn(50)->create();
        $latest = License::factory()->for($vessel)->expiringIn(300)->create();

        $rows = app(AuthorizedList::class)->rows();

        $this->assertSame([$latest->id], $rows->pluck('id')->all());
        $this->assertSame($latest->id, $vessel->currentLicense->id);
    }

    public function test_publishing_creates_a_numbered_frozen_pdf_and_the_row_is_immutable(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        License::factory()->expiringIn(200)->create();

        $this->post(route('licenses.authorized.publish'))->assertRedirect(route('licenses.authorized.index'));
        $this->post(route('licenses.authorized.publish'));

        $this->assertSame([1, 2], AuthorizedListPublication::orderBy('version')->pluck('version')->all());
        $publication = AuthorizedListPublication::where('version', 2)->sole();
        $this->assertTrue($publication->document->is_frozen);
        $this->assertStringStartsWith('%PDF', Storage::disk(DocumentStore::DISK)->get($publication->document->path));
        $this->get(route('licenses.authorized.download', $publication))->assertOk();

        $this->expectException(QueryException::class);
        $publication->update(['version' => 99]);
    }

    public function test_a_publication_cannot_be_deleted(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        $this->post(route('licenses.authorized.publish'));

        $this->expectException(QueryException::class);
        AuthorizedListPublication::sole()->delete();
    }

    public function test_freshness_tells_when_the_list_changed_since_the_last_publication(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        $list = app(AuthorizedList::class);

        $this->assertTrue($list->isStale());

        License::factory()->expiringIn(200)->create();
        $this->post(route('licenses.authorized.publish'));
        $this->travel(1)->minute();
        $this->assertFalse($list->isStale());

        License::factory()->expiringIn(100)->create();
        $this->assertTrue($list->isStale());
        $this->get(route('licenses.authorized.index'))->assertOk()->assertSee(__('licenses::authorized.stale'));
    }

    public function test_agents_read_the_list_but_cannot_publish(): void
    {
        $this->signInAs(Role::Agent);
        License::factory()->expiringIn(200)->create(['number' => 'LIC-AUTH-1']);

        $this->get(route('licenses.authorized.index'))->assertOk()->assertSee('LIC-AUTH-1')->assertDontSee(__('licenses::authorized.publish'));
        $this->post(route('licenses.authorized.publish'))->assertForbidden();
        $this->assertSame(0, AuthorizedListPublication::count());
    }

    #[Assumes('H-4a-8')]
    public function test_the_annual_register_lists_the_licenses_of_the_year_and_is_regenerated_in_place(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::Director);
        License::factory()->create(['issued_on' => '2025-03-01', 'expires_on' => '2026-02-28']);

        $this->post(route('licenses.register.generate'), ['year' => 2025])->assertRedirect(route('licenses.register.index'));
        $this->post(route('licenses.register.generate'), ['year' => 2025]);

        $this->assertSame(1, LicenseRegister::count());
        $register = LicenseRegister::findOrFail(2025);
        $this->assertStringStartsWith('%PDF', Storage::disk(DocumentStore::DISK)->get($register->document->path));
        $this->get(route('licenses.register.download', 2025))->assertOk();
        $this->get(route('licenses.register.index'))->assertOk()->assertSee('2025');
        $this->post(route('licenses.register.generate'), ['year' => 1990])->assertSessionHasErrors('year');
    }
}
