<?php

namespace Modules\Movements\Tests\Feature;

use App\Models\EezDeclaration;
use App\Models\EezDeclarationCatch;
use App\Models\EezDeclarationCheck;
use App\Models\EntryExitAlert;
use App\Models\EntryExitLink;
use App\Models\MissedDeclaration;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EezSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_factory_produces_a_valid_row(): void
    {
        foreach ([EezDeclaration::class, EezDeclarationCatch::class, EezDeclarationCheck::class, EntryExitLink::class, MissedDeclaration::class, EntryExitAlert::class] as $model) {
            $this->assertTrue($model::factory()->create()->exists, $model);
        }
    }

    public function test_enumerated_columns_are_constrained_by_the_database(): void
    {
        $declaration = EezDeclaration::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('eez_declarations')->where('id', $declaration->id)->update(['status' => 'nonsense']);
    }

    public function test_the_alert_table_read_by_the_watch_keeps_its_name_and_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('entry_exit_alerts', ['vessel_id', 'eez_declaration_id', 'direction', 'alerted_at', 'status']));
    }
}
