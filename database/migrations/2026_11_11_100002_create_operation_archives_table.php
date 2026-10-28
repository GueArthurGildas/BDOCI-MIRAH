<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CHILD_TABLES = ['operation_contexts', 'operation_vessel', 'operation_user', 'operation_events', 'operation_instructions'];

    public function up(): void
    {
        Schema::create('operation_archives', function (Blueprint $table) {
            $table->foreignId('operation_id')->primary()->constrained('operations');
            $table->timestamp('archived_at');
        });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION forbid_archive_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'operation archives are permanent';
            END;
            $$;

            CREATE TRIGGER operation_archives_permanent BEFORE UPDATE OR DELETE ON operation_archives
                FOR EACH ROW EXECUTE FUNCTION forbid_archive_change();

            CREATE OR REPLACE FUNCTION forbid_archived_operation_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF EXISTS (SELECT 1 FROM operation_archives WHERE operation_id = OLD.id) THEN
                    RAISE EXCEPTION 'operation is archived: read-only';
                END IF;
                IF TG_OP = 'DELETE' THEN
                    RETURN OLD;
                END IF;
                RETURN NEW;
            END;
            $$;

            CREATE TRIGGER operations_archived_read_only BEFORE UPDATE OR DELETE ON operations
                FOR EACH ROW EXECUTE FUNCTION forbid_archived_operation_change();

            CREATE OR REPLACE FUNCTION forbid_archived_operation_child_change() RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE
                target bigint;
            BEGIN
                IF TG_OP = 'DELETE' THEN
                    target := OLD.operation_id;
                ELSE
                    target := NEW.operation_id;
                END IF;
                IF EXISTS (SELECT 1 FROM operation_archives WHERE operation_id = target) THEN
                    RAISE EXCEPTION 'operation is archived: read-only';
                END IF;
                IF TG_OP = 'DELETE' THEN
                    RETURN OLD;
                END IF;
                RETURN NEW;
            END;
            $$;
        SQL);

        foreach (self::CHILD_TABLES as $table) {
            DB::unprepared("CREATE TRIGGER {$table}_archived_read_only BEFORE INSERT OR UPDATE OR DELETE ON {$table} FOR EACH ROW EXECUTE FUNCTION forbid_archived_operation_child_change()");
        }
    }

    public function down(): void
    {
        foreach (self::CHILD_TABLES as $table) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$table}_archived_read_only ON {$table}");
        }

        DB::unprepared('DROP TRIGGER IF EXISTS operations_archived_read_only ON operations');
        DB::unprepared('DROP FUNCTION IF EXISTS forbid_archived_operation_child_change()');
        DB::unprepared('DROP FUNCTION IF EXISTS forbid_archived_operation_change()');
        DB::unprepared('DROP TRIGGER IF EXISTS operation_archives_permanent ON operation_archives');
        DB::unprepared('DROP FUNCTION IF EXISTS forbid_archive_change()');
        Schema::dropIfExists('operation_archives');
    }
};
