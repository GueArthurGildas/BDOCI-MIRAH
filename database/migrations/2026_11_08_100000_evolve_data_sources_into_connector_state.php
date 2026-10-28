<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MODES = ['database' => 'direct_read', 'file' => 'import'];

    private const INDICATORS = ['operational' => 'green', 'degraded' => 'orange', 'down' => 'red'];

    public function up(): void
    {
        Schema::table('data_sources', function (Blueprint $table) {
            $table->renameColumn('key', 'code');
            $table->renameColumn('kind', 'mode');
            $table->renameColumn('status', 'indicator_status');
        });

        Schema::table('data_sources', function (Blueprint $table) {
            $table->string('code', 40)->change();
            $table->string('mode', 40)->change();
            $table->string('indicator_status', 40)->default('red')->change();
            $table->text('last_error')->nullable();
            $table->boolean('is_stale')->default(false);
        });

        foreach (self::MODES as $old => $new) {
            DB::table('data_sources')->where('mode', $old)->update(['mode' => $new]);
        }

        foreach (self::INDICATORS as $old => $new) {
            DB::table('data_sources')->where('indicator_status', $old)->update(['indicator_status' => $new]);
        }

        DB::statement("ALTER TABLE data_sources ADD CONSTRAINT data_sources_mode_check CHECK (mode IN ('api', 'import', 'scraping', 'direct_read'))");
        DB::statement("ALTER TABLE data_sources ADD CONSTRAINT data_sources_indicator_status_check CHECK (indicator_status IN ('green', 'orange', 'red'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE data_sources DROP CONSTRAINT IF EXISTS data_sources_mode_check');
        DB::statement('ALTER TABLE data_sources DROP CONSTRAINT IF EXISTS data_sources_indicator_status_check');

        foreach (array_flip(self::MODES) as $old => $new) {
            DB::table('data_sources')->where('mode', $old)->update(['mode' => $new]);
        }

        DB::table('data_sources')->whereIn('mode', ['scraping'])->update(['mode' => 'api']);

        foreach (array_flip(self::INDICATORS) as $old => $new) {
            DB::table('data_sources')->where('indicator_status', $old)->update(['indicator_status' => $new]);
        }

        Schema::table('data_sources', function (Blueprint $table) {
            $table->dropColumn(['last_error', 'is_stale']);
        });

        Schema::table('data_sources', function (Blueprint $table) {
            $table->string('code')->change();
            $table->string('mode')->change();
            $table->string('indicator_status')->default(null)->change();
        });

        Schema::table('data_sources', function (Blueprint $table) {
            $table->renameColumn('code', 'key');
            $table->renameColumn('mode', 'kind');
            $table->renameColumn('indicator_status', 'status');
        });
    }
};
