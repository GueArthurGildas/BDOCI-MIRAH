<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('status', 40)->default('active')->after('valid_until');
            $table->foreignId('entered_by')->nullable()->after('status')->constrained('users');
            $table->string('origin', 40)->default('manual')->after('entered_by');
        });

        DB::statement("UPDATE licenses SET status = 'suspended' WHERE suspended_at IS NOT NULL");
        DB::statement('ALTER TABLE licenses RENAME COLUMN type TO segment');
        DB::statement('ALTER TABLE licenses RENAME COLUMN valid_from TO issued_on');
        DB::statement('ALTER TABLE licenses RENAME COLUMN valid_until TO expires_on');
        DB::statement('ALTER TABLE licenses ALTER COLUMN issued_on DROP NOT NULL');
        DB::statement('ALTER TABLE licenses ALTER COLUMN segment DROP NOT NULL');
        DB::statement('ALTER TABLE licenses ALTER COLUMN number TYPE varchar(80)');
        DB::statement('ALTER INDEX licenses_valid_until_index RENAME TO licenses_expires_on_index');
        DB::statement("ALTER TABLE licenses ADD CONSTRAINT licenses_segment_check CHECK (segment IS NULL OR segment IN ('artisanal', 'semi_industrial', 'industrial'))");
        DB::statement("ALTER TABLE licenses ADD CONSTRAINT licenses_status_check CHECK (status IN ('active', 'suspended', 'expired', 'revoked'))");
        DB::statement("ALTER TABLE licenses ADD CONSTRAINT licenses_origin_check CHECK (origin IN ('import', 'batch', 'manual'))");
        DB::statement('CREATE INDEX licenses_status_index ON licenses (status)');

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable();
        });

        DB::statement("UPDATE licenses SET suspended_at = now() WHERE status = 'suspended'");
        DB::statement('DROP INDEX IF EXISTS licenses_status_index');

        foreach (['segment', 'status', 'origin'] as $column) {
            DB::statement("ALTER TABLE licenses DROP CONSTRAINT IF EXISTS licenses_{$column}_check");
        }

        DB::statement('ALTER INDEX licenses_expires_on_index RENAME TO licenses_valid_until_index');
        DB::statement('ALTER TABLE licenses RENAME COLUMN expires_on TO valid_until');
        DB::statement('ALTER TABLE licenses RENAME COLUMN issued_on TO valid_from');
        DB::statement("UPDATE licenses SET segment = 'industrial' WHERE segment IS NULL");
        DB::statement('UPDATE licenses SET valid_from = valid_until WHERE valid_from IS NULL');
        DB::statement('ALTER TABLE licenses RENAME COLUMN segment TO type');
        DB::statement('ALTER TABLE licenses ALTER COLUMN type SET NOT NULL');
        DB::statement('ALTER TABLE licenses ALTER COLUMN valid_from SET NOT NULL');

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entered_by');
            $table->dropColumn(['status', 'origin']);
        });
    }
};
