<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class GlossaryTest extends TestCase
{
    use RefreshDatabase;

    private const FRAMEWORK_TABLES = [
        'migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'sessions', 'password_reset_tokens', 'spatial_ref_sys',
    ];

    public function test_every_application_table_appears_in_the_glossary(): void
    {
        $glossary = file_get_contents(base_path('docs/GLOSSAIRE.md'));

        $missing = collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn (string $table) => in_array($table, self::FRAMEWORK_TABLES, true))
            ->reject(fn (string $table) => str_contains($glossary, "`{$table}`"))
            ->values()
            ->all();

        $this->assertSame([], $missing, 'Tables missing from docs/GLOSSAIRE.md');
    }

    public function test_every_table_of_the_physical_schema_is_documented_in_both_glossaries(): void
    {
        preg_match_all('/^CREATE TABLE (\w+) \(/m', file_get_contents(base_path('docs/schema/schema_v2.sql')), $matches);

        $tables = collect($matches[1])->reject(fn (string $table) => Str::startsWith($table, 'positions_'))->values();
        $glossary = file_get_contents(base_path('docs/GLOSSAIRE.md'));
        $columns = file_get_contents(base_path('docs/schema/GLOSSAIRE_COLONNES.md'));

        $this->assertCount(175, $tables);
        $this->assertSame([], $tables->reject(fn (string $table) => str_contains($glossary, "`{$table}`"))->values()->all());
        $this->assertSame([], $tables->reject(fn (string $table) => str_contains($columns, "### `{$table}`"))->values()->all());
    }
}
