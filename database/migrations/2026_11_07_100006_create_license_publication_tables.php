<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_registers', function (Blueprint $table) {
            $table->smallInteger('year')->primary();
            $table->foreignId('document_id')->nullable()->constrained();
            $table->timestamp('generated_at')->nullable();
        });

        Schema::create('authorized_list_publications', function (Blueprint $table) {
            $table->id();
            $table->integer('version')->unique();
            $table->foreignId('document_id')->nullable()->constrained();
            $table->timestamp('published_at');
            $table->timestamps();
        });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION forbid_authorized_list_publication_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'an authorized list publication cannot be modified or deleted';
            END;
            $$;

            DROP TRIGGER IF EXISTS authorized_list_publications_immutable ON authorized_list_publications;
            CREATE TRIGGER authorized_list_publications_immutable BEFORE UPDATE OR DELETE ON authorized_list_publications
                FOR EACH ROW EXECUTE FUNCTION forbid_authorized_list_publication_change();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS authorized_list_publications_immutable ON authorized_list_publications');
        Schema::dropIfExists('authorized_list_publications');
        Schema::dropIfExists('license_registers');
        DB::unprepared('DROP FUNCTION IF EXISTS forbid_authorized_list_publication_change()');
    }
};
