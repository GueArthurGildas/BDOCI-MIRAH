<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION forbid_locked_logbook_entry_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF (SELECT status FROM logbook_days WHERE id = OLD.logbook_day_id) = 'locked' THEN
                    RAISE EXCEPTION 'logbook day is locked: use a corrective entry';
                END IF;
                RETURN NEW;
            END;
            $$;

            DROP TRIGGER IF EXISTS logbook_entries_locked ON logbook_entries;
            CREATE TRIGGER logbook_entries_locked BEFORE UPDATE ON logbook_entries
                FOR EACH ROW EXECUTE FUNCTION forbid_locked_logbook_entry_change();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS logbook_entries_locked ON logbook_entries');
        DB::unprepared('DROP FUNCTION IF EXISTS forbid_locked_logbook_entry_change()');
    }
};
