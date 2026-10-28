<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION activity_log_append_only() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'activity_log is append-only';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER activity_log_append_only
            BEFORE UPDATE OR DELETE ON activity_log
            FOR EACH ROW EXECUTE FUNCTION activity_log_append_only();
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS activity_log_append_only ON activity_log; DROP FUNCTION IF EXISTS activity_log_append_only();');
    }
};
