<?php

namespace Modules\Licenses\Tests\Feature;

use App\Enums\LicenseImportLineStatus;
use App\Enums\LicenseImportStatus;
use App\Enums\LicenseOrigin;
use App\Models\Gear;
use App\Models\License;
use App\Models\LicenseImport;
use App\Models\LicenseImportLine;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\Zone;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC28', 'UC02', partial: true)]
class BatchImportTest extends TestCase
{
    use RefreshDatabase;

    private const HEADER = 'registration_number;number;segment;issued_on;expires_on;zones;gears;species;conditions';

    private function upload(array $rows)
    {
        $content = implode("\n", [self::HEADER, ...$rows]);

        return $this->post(route('licenses.imports.file'), ['file' => UploadedFile::fake()->createWithContent('licences.csv', $content)]);
    }

    private function row(string $registration, string $number, ?string $expiresOn = null): string
    {
        return implode(';', [$registration, $number, 'industrial', today()->toDateString(), $expiresOn ?? today()->addYear()->toDateString(), '', '', '', '']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(DocumentStore::DISK);
    }

    #[Assumes('H-4a-5')]
    public function test_a_csv_file_is_read_into_lines_with_rejected_lines_kept_and_a_reason(): void
    {
        $this->signInAs(Role::TeamLead);
        $zone = Zone::factory()->create(['name' => 'ZEE Ouest']);
        $gear = Gear::factory()->create();
        $species = Species::factory()->create();
        [$first, $second] = Vessel::factory()->count(2)->create();
        License::factory()->create(['number' => 'LIC-EXISTING']);

        $this->upload([
            implode(';', [$first->registration_number, 'LIC-A', 'industrial', '2026-01-01', '2026-12-31', 'ZEE Ouest', $gear->code_fao, $species->fao_code, 'Observer|Daylight only']),
            $this->row('NO-SUCH-VESSEL', 'LIC-B'),
            $this->row($second->registration_number, 'LIC-EXISTING'),
            $this->row($second->registration_number, 'LIC-C', '2020-01-01'),
            implode(';', [$second->registration_number, 'LIC-D', 'industrial', '2026-01-01', '2026-12-31', 'Atlantis', '', '', '']),
        ])->assertRedirect();

        $import = LicenseImport::sole();
        $this->assertSame(LicenseImportStatus::Preview, $import->status);
        $this->assertNotNull($import->document_id);

        $lines = $import->lines->keyBy(fn ($line) => $line->data['number']);
        $this->assertSame(LicenseImportLineStatus::ToValidate, $lines['LIC-A']->status);
        $this->assertSame(['Observer', 'Daylight only'], $lines['LIC-A']->data['conditions']);
        $this->assertSame('unknown_vessel', $lines['LIC-B']->rejection_reason);
        $this->assertSame('duplicate_number', $lines['LIC-EXISTING']->rejection_reason);
        $this->assertSame('invalid_dates', $lines['LIC-C']->rejection_reason);
        $this->assertSame('unknown_zone', $lines['LIC-D']->rejection_reason);
        $this->assertSame($zone->id, Zone::where('name', 'ZEE Ouest')->value('id'));
        $this->assertSame(1, License::count());
    }

    public function test_the_team_lead_validates_the_whole_batch_in_one_action(): void
    {
        $lead = $this->signInAs(Role::TeamLead);
        [$first, $second, $third] = Vessel::factory()->count(3)->create();

        $this->upload([
            $this->row($first->registration_number, 'LIC-1'),
            $this->row($second->registration_number, 'LIC-2'),
            $this->row($third->registration_number, 'LIC-1'),
        ]);
        $import = LicenseImport::sole();

        $this->post(route('licenses.imports.validate', $import))->assertRedirect(route('licenses.imports.show', $import));

        $this->assertSame(2, License::count());
        $this->assertSame(LicenseOrigin::Import, License::where('number', 'LIC-1')->sole()->origin);
        $this->assertSame($lead->id, License::where('number', 'LIC-1')->sole()->entered_by);
        $this->assertSame(2, VesselEvent::where('type', 'license.recorded')->count());
        $this->assertSame(LicenseImportStatus::Valid, $import->fresh()->status);
        $this->assertNotNull($import->fresh()->validated_at);

        $rejected = $import->lines()->where('status', 'rejected')->sole();
        $this->assertSame('duplicate_number', $rejected->rejection_reason);
        $this->assertNull($rejected->license_id);
        $this->assertSame(2, $import->lines()->where('status', 'valid')->whereNotNull('license_id')->count());
        $this->post(route('licenses.imports.validate', $import))->assertSessionHasErrors('import');
    }

    public function test_a_line_can_be_completed_and_validated_vessel_by_vessel(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessels = Vessel::factory()->count(2)->create();

        $this->post(route('licenses.imports.batch'), ['vessel_ids' => $vessels->pluck('id')->all()])->assertRedirect();
        $import = LicenseImport::sole();
        $this->assertNull($import->document_id);
        $this->assertSame(2, $import->lines()->count());

        [$first, $second] = $import->lines->all();
        $this->post(route('licenses.imports.lines.validate', $first), ['number' => '', 'expires_on' => ''])->assertRedirect();
        $this->assertSame(LicenseImportLineStatus::Rejected, $first->fresh()->status);
        $this->assertSame('missing_number', $first->fresh()->rejection_reason);

        $this->put(route('licenses.imports.lines.update', $first), ['number' => 'LIC-BATCH-1', 'segment' => 'artisanal', 'issued_on' => today()->toDateString(), 'expires_on' => today()->addYear()->toDateString()])->assertRedirect();
        $this->assertSame(LicenseImportLineStatus::ToValidate, $first->fresh()->status);

        $this->post(route('licenses.imports.lines.validate', $first), ['number' => 'LIC-BATCH-1', 'segment' => 'artisanal', 'expires_on' => today()->addYear()->toDateString()])->assertRedirect();
        $this->assertSame(LicenseImportLineStatus::Valid, $first->fresh()->status);
        $this->assertSame(LicenseOrigin::Batch, License::sole()->origin);
        $this->assertSame(LicenseImportStatus::Preview, $import->fresh()->status);

        $this->post(route('licenses.imports.lines.validate', $second), ['number' => 'LIC-BATCH-2', 'expires_on' => today()->addYear()->toDateString()]);
        $this->assertSame(LicenseImportStatus::Valid, $import->fresh()->status);
        $this->put(route('licenses.imports.lines.update', $first), ['number' => 'x'])->assertSessionHasErrors('line');
    }

    public function test_an_import_can_be_cancelled_and_then_creates_nothing(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $this->upload([$this->row($vessel->registration_number, 'LIC-X')]);
        $import = LicenseImport::sole();

        $this->post(route('licenses.imports.cancel', $import))->assertRedirect();
        $this->assertSame(LicenseImportStatus::Cancelled, $import->fresh()->status);
        $this->post(route('licenses.imports.validate', $import))->assertSessionHasErrors('import');
        $this->assertSame(0, License::count());
    }

    public function test_an_empty_file_is_refused(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->upload([])->assertSessionHasErrors('file');
        $this->assertSame(0, LicenseImport::count());
    }

    public function test_agents_cannot_import_or_validate(): void
    {
        $this->signInAs(Role::Agent);
        $line = LicenseImportLine::factory()->create();

        $this->upload([])->assertForbidden();
        $this->post(route('licenses.imports.validate', $line->licenseImport))->assertForbidden();
        $this->post(route('licenses.imports.lines.validate', $line))->assertForbidden();
        $this->get(route('licenses.imports.index'))->assertOk()->assertDontSee(route('licenses.imports.file'));
        $this->assertSame(0, License::count());
    }

    public function test_the_import_screens_show_lines_with_their_rejection_reason(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $this->upload([$this->row($vessel->registration_number, 'LIC-OK'), $this->row('NO-SUCH-VESSEL', 'LIC-KO')]);
        $import = LicenseImport::sole();

        $this->get(route('licenses.imports.index'))->assertOk()->assertSee(route('licenses.imports.show', $import));
        $this->get(route('licenses.imports.show', $import))->assertOk()->assertSee('LIC-OK')->assertSee(__('licenses::imports.rejections.unknown_vessel'));
    }
}
