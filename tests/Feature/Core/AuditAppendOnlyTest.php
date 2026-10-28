<?php

namespace Tests\Feature\Core;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AuditAppendOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_entries_cannot_be_modified_or_deleted(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('The append-only trigger is PostgreSQL specific.');
        }

        activity('test')->log('created');
        $entry = Activity::query()->firstOrFail();

        try {
            DB::table('activity_log')->where('id', $entry->id)->update(['description' => 'tampered']);
            $this->fail('Update should be rejected.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('append-only', $e->getMessage());
        }

        $this->expectException(QueryException::class);
        DB::table('activity_log')->where('id', $entry->id)->delete();
    }
}
