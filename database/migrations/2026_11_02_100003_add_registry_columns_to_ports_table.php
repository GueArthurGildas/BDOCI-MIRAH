<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->unique()->after('id');
            $table->boolean('is_designated')->default(false)->after('zone_id');
        });

        DB::statement("UPDATE ports SET code = upper(left(regexp_replace(locality, '[^A-Za-z]', '', 'g'), 3)) || id WHERE code IS NULL");
        DB::statement('ALTER TABLE ports ALTER COLUMN code SET NOT NULL');
    }

    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_designated']);
        });
    }
};
