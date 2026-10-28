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
            CREATE OR REPLACE FUNCTION forbid_activity_log_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'activity_log is append-only';
            END;
            $$;

            DROP TRIGGER IF EXISTS activity_log_no_update_delete ON activity_log;
            CREATE TRIGGER activity_log_no_update_delete BEFORE UPDATE OR DELETE ON activity_log
                FOR EACH ROW EXECUTE FUNCTION forbid_activity_log_change();

            DROP TRIGGER IF EXISTS activity_log_no_truncate ON activity_log;
            CREATE TRIGGER activity_log_no_truncate BEFORE TRUNCATE ON activity_log
                FOR EACH STATEMENT EXECUTE FUNCTION forbid_activity_log_change();
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS activity_log_no_truncate ON activity_log;
            DROP TRIGGER IF EXISTS activity_log_no_update_delete ON activity_log;
            DROP FUNCTION IF EXISTS forbid_activity_log_change();
        SQL);
    }
};
