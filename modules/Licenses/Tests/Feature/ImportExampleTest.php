<?php

namespace Modules\Licenses\Tests\Feature;

use App\Enums\LicenseImportLineStatus;
use App\Enums\LicenseImportStatus;
use App\Models\Gear;
use App\Models\License;
use App\Models\LicenseImport;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Licenses\Support\CsvReader;
use Modules\Licenses\Support\ImportExample;
use Tests\TestCase;

class ImportExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(DocumentStore::DISK);
    }

    private function reference(): void
    {
        Vessel::factory()->count(3)->create(['category' => 'fishing']);
        Zone::factory()->count(2)->create();
        Gear::factory()->count(2)->create();
        Species::factory()->count(2)->create();
    }

    public function test_the_example_file_explains_every_field_and_is_prefilled_with_real_data(): void
    {
        $this->signInAs(Role::Director);
        $this->reference();

        $response = $this->get(route('licenses.imports.example'))->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(ImportExample::FILENAME, $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith("\xEF\xBB\xBF# ", $content);

        foreach (CsvReader::COLUMNS as $column) {
            $this->assertStringContainsString("# {$column} : ", $content);
        }

        $this->assertStringContainsString(implode(';', CsvReader::COLUMNS), $content);
        $this->assertCount(3, app(CsvReader::class)->read($content));
        $this->assertSame(Vessel::pluck('registration_number')->sort()->values()->all(), collect(app(CsvReader::class)->read($content))->pluck('data.registration_number')->sort()->values()->all());
    }

    public function test_comment_lines_are_ignored_when_reading(): void
    {
        $rows = app(CsvReader::class)->read("# explication\n# autre\nregistration_number;number;expires_on\nAN-1;LIC-1;2026-12-31\n");

        $this->assertCount(1, $rows);
        $this->assertSame('LIC-1', $rows[0]['data']['number']);
    }

    public function test_a_file_made_only_of_comments_is_empty(): void
    {
        $this->assertSame([], app(CsvReader::class)->read("# seulement\n# des commentaires\n"));
    }

    public function test_the_downloaded_example_can_be_imported_as_is_and_validated(): void
    {
        $this->signInAs(Role::Director);
        $this->reference();
        $content = $this->get(route('licenses.imports.example'))->getContent();

        $this->post(route('licenses.imports.file'), ['file' => UploadedFile::fake()->createWithContent('exemple.csv', $content)])->assertRedirect();

        $import = LicenseImport::sole();
        $this->assertCount(3, $import->lines);
        $this->assertSame([LicenseImportLineStatus::ToValidate], $import->lines->pluck('status')->unique()->all());
        $this->assertSame([null], $import->lines->pluck('rejection_reason')->unique()->all());

        $this->post(route('licenses.imports.validate', $import))->assertRedirect();

        $this->assertSame(3, License::count());
        $this->assertSame(LicenseImportStatus::Valid, $import->fresh()->status);
    }

    public function test_importing_the_example_twice_is_rejected_line_by_line(): void
    {
        $this->signInAs(Role::Director);
        $this->reference();
        $content = $this->get(route('licenses.imports.example'))->getContent();
        $upload = fn () => $this->post(route('licenses.imports.file'), ['file' => UploadedFile::fake()->createWithContent('exemple.csv', $content)]);

        $upload();
        $this->post(route('licenses.imports.validate', LicenseImport::sole()))->assertRedirect();
        $upload();

        $second = LicenseImport::latest('id')->first();
        $this->assertSame(['duplicate_number'], $second->lines->pluck('rejection_reason')->unique()->all());
    }

    public function test_the_import_page_offers_the_example_and_its_field_guide(): void
    {
        $this->signInAs(Role::Director);
        $this->reference();

        $this->get(route('licenses.imports.index'))
            ->assertOk()
            ->assertSee(route('licenses.imports.example'), false)
            ->assertSee(__('licenses::imports.example.title'))
            ->assertSee('<code>expires_on</code>', false)
            ->assertViewHas('exampleFields', fn (array $fields) => count($fields) === 9 && collect($fields)->where('required', true)->pluck('field')->all() === ['registration_number', 'number', 'expires_on']);
    }

    public function test_the_example_needs_to_be_signed_in(): void
    {
        $this->get(route('licenses.imports.example'))->assertRedirect(route('login'));
    }
}
