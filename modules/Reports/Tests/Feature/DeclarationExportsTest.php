<?php

namespace Modules\Reports\Tests\Feature;

use App\Models\EezDeclaration;
use App\Models\ErsCatchLine;
use App\Models\ErsDeclaration;
use App\Models\ExportLog;
use App\Models\Species;
use App\Models\Vessel;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeclarationExportsTest extends TestCase
{
    use RefreshDatabase;

    private function eez(Vessel $vessel, string $receivedAt, array $attributes = []): EezDeclaration
    {
        return EezDeclaration::factory()->create([
            'vessel_id' => $vessel->id,
            'received_at' => $receivedAt,
            'declared_name' => $vessel->name,
            'destination' => 'Port de '.$vessel->name,
            ...$attributes,
        ]);
    }

    public function test_eez_declarations_are_exported_with_all_their_fields(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create(['name' => 'NAVIRE ZEE']);
        $this->eez($vessel, '2026-03-10 08:00:00', ['declared_imo' => 'IMO7654321', 'route' => 'Nord-est']);

        $response = $this->get(route('reports.exports.download', ['type' => 'eez_declarations', 'format' => 'csv']))->assertOk();
        $csv = $response->getContent();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        foreach (['NAVIRE ZEE', 'IMO7654321', 'Nord-est', 'Port de NAVIRE ZEE', '2026-03-10 08:00'] as $expected) {
            $this->assertStringContainsString($expected, $csv);
        }

        foreach (['crossed_at', 'position', 'declared_flag', 'entered_by', 'catches_kg'] as $column) {
            $this->assertStringContainsString(__("reports::reports.columns.{$column}"), $csv);
        }
    }

    public function test_the_export_can_be_limited_to_a_period_or_to_one_vessel(): void
    {
        $this->signInAs(Role::Director);
        $target = Vessel::factory()->create(['name' => 'NAVIRE CIBLE']);
        $other = Vessel::factory()->create(['name' => 'NAVIRE AUTRE']);
        $this->eez($target, '2026-03-10 08:00:00');
        $this->eez($target, '2026-07-10 08:00:00');
        $this->eez($other, '2026-03-12 08:00:00');
        $download = fn (array $filters) => $this->get(route('reports.exports.download', ['type' => 'eez_declarations', 'format' => 'csv', ...$filters]))->assertOk()->getContent();

        $byVessel = $download(['vessel' => $target->id]);
        $this->assertStringContainsString('NAVIRE CIBLE', $byVessel);
        $this->assertStringNotContainsString('NAVIRE AUTRE', $byVessel);
        $this->assertSame(3, count(array_filter(preg_split('/\r\n|\n/', trim($byVessel)))));

        $byPeriod = $download(['from' => '2026-03-01', 'to' => '2026-03-31']);
        $this->assertStringContainsString('NAVIRE AUTRE', $byPeriod);
        $this->assertStringNotContainsString('2026-07-10', $byPeriod);

        $both = $download(['vessel' => $target->id, 'from' => '2026-03-01', 'to' => '2026-03-31']);
        $this->assertStringNotContainsString('NAVIRE AUTRE', $both);
        $this->assertStringNotContainsString('2026-07-10', $both);
    }

    public function test_ers_declarations_are_exported_one_row_per_catch_line(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create(['name' => 'NAVIRE ERS']);
        $declaration = ErsDeclaration::factory()->create(['vessel_id' => $vessel->id, 'declared_at' => '2026-04-02 09:00:00']);
        ErsCatchLine::factory()->create(['ers_declaration_id' => $declaration->id, 'species_id' => Species::factory()->create(['name' => 'Thon obèse'])->id, 'live_weight_kg' => 1234.5]);
        ErsCatchLine::factory()->create(['ers_declaration_id' => $declaration->id, 'species_id' => Species::factory()->create(['name' => 'Listao'])->id, 'live_weight_kg' => 800]);
        ErsDeclaration::factory()->create(['vessel_id' => $vessel->id, 'declared_at' => '2026-04-03 09:00:00']);

        $csv = $this->get(route('reports.exports.download', ['type' => 'ers_declarations', 'format' => 'csv', 'vessel' => $vessel->id]))->assertOk()->getContent();

        $this->assertStringContainsString('Thon obèse', $csv);
        $this->assertStringContainsString('Listao', $csv);
        $this->assertStringContainsString('1234.5', $csv);
        $this->assertSame(3, substr_count($csv, 'NAVIRE ERS'));
    }

    public function test_the_pdf_format_works_too(): void
    {
        $this->signInAs(Role::Director);
        $this->eez(Vessel::factory()->create(), '2026-03-10 08:00:00');

        $this->get(route('reports.exports.download', ['type' => 'eez_declarations', 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_an_export_is_logged_with_its_vessel_and_period_filters(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        $this->eez($vessel, '2026-03-10 08:00:00');

        $this->get(route('reports.exports.download', ['type' => 'eez_declarations', 'format' => 'csv', 'vessel' => $vessel->id, 'from' => '2026-03-01']))->assertOk();

        $log = ExportLog::sole();
        $this->assertSame('eez_declarations', $log->subject);
        $this->assertSame(Permission::ReportsExportEezDeclarations->value, $log->permission);
        $this->assertSame(1, $log->row_count);
        $this->assertEquals(['from' => '2026-03-01', 'vessel' => (string) $vessel->id], collect($log->filters)->map(fn ($value) => (string) $value)->all());
    }

    public function test_exports_need_their_own_permission(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('reports.exports.download', ['type' => 'eez_declarations', 'format' => 'csv']))->assertForbidden();
        $this->get(route('reports.exports.download', ['type' => 'ers_declarations', 'format' => 'csv']))->assertForbidden();

        $this->signInWithPermissions(Permission::ReportsExportEezDeclarations);
        $this->get(route('reports.exports.download', ['type' => 'eez_declarations', 'format' => 'csv']))->assertOk();
        $this->get(route('reports.exports.download', ['type' => 'ers_declarations', 'format' => 'csv']))->assertForbidden();
    }

    public function test_bad_filters_are_rejected(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('reports.exports.download', ['type' => 'eez_declarations', 'format' => 'csv', 'vessel' => 999999]))->assertSessionHasErrors('vessel');
        $this->get(route('reports.exports.download', ['type' => 'eez_declarations', 'format' => 'csv', 'from' => '2026-05-10', 'to' => '2026-05-01']))->assertSessionHasErrors('to');
    }

    public function test_the_exports_page_offers_the_new_types_with_a_vessel_filter(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('reports.exports.index'))
            ->assertOk()
            ->assertSee(__('reports::reports.exports.types.eez_declarations'))
            ->assertSee(__('reports::reports.exports.types.ers_declarations'))
            ->assertSee('name="vessel"', false);
    }
}
