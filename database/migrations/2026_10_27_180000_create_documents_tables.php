<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mime_type', 100)->nullable();
            $table->string('path', 500);
            $table->char('sha256_fingerprint', 64)->nullable();
            $table->boolean('is_frozen')->default(false);
            $table->foreignId('filed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('document_links', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('object_type', 80);
            $table->unsignedBigInteger('object_id');

            $table->primary(['document_id', 'object_type', 'object_id']);
            $table->index(['object_type', 'object_id']);
        });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION forbid_frozen_document_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF OLD.is_frozen THEN
                    RAISE EXCEPTION 'a frozen document cannot be modified or deleted';
                END IF;
                IF TG_OP = 'DELETE' THEN
                    RETURN OLD;
                END IF;
                RETURN NEW;
            END;
            $$;

            DROP TRIGGER IF EXISTS documents_frozen_guard ON documents;
            CREATE TRIGGER documents_frozen_guard BEFORE UPDATE OR DELETE ON documents
                FOR EACH ROW EXECUTE FUNCTION forbid_frozen_document_change();
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('document_links');
        Schema::dropIfExists('documents');
        DB::unprepared('DROP FUNCTION IF EXISTS forbid_frozen_document_change()');
    }
};
