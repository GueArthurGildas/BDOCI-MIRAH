<?php

namespace Tests\Feature\Kernel;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AuditLogImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_entries_can_be_appended_but_never_changed_deleted_or_truncated(): void
    {
        activity('tests')->event('created')->log('created');
        $entry = Activity::sole();

        $this->assertForbidden(fn () => $entry->update(['description' => 'edited']));
        $this->assertForbidden(fn () => $entry->delete());
        $this->assertForbidden(fn () => DB::table('activity_log')->truncate());

        $this->assertSame('created', $entry->fresh()->description);
        activity('tests')->event('created')->log('another');
        $this->assertSame(2, Activity::count());
    }

    private function assertForbidden(callable $change): void
    {
        try {
            DB::transaction($change);
            $this->fail('The audit log accepted a change.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('append-only', $exception->getMessage());
        }
    }
}
