<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('subject', 120);
            $table->string('permission', 60);
            $table->string('format', 20);
            $table->jsonb('filters')->nullable();
            $table->integer('row_count')->default(0);
            $table->timestamp('exported_at');
            $table->timestamps();

            $table->index(['user_id', 'exported_at']);
        });

        DB::statement("ALTER TABLE export_log ADD CONSTRAINT export_log_format_check CHECK (format IN ('csv', 'pdf'))");

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION forbid_export_log_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'export_log is append-only';
            END;
            $$;

            CREATE TRIGGER export_log_append_only BEFORE UPDATE OR DELETE ON export_log
                FOR EACH ROW EXECUTE FUNCTION forbid_export_log_change();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS export_log_append_only ON export_log');
        DB::unprepared('DROP FUNCTION IF EXISTS forbid_export_log_change()');
        Schema::dropIfExists('export_log');
    }
};
