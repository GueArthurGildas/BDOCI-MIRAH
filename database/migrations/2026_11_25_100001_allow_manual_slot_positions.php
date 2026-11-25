<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE slot_positions DROP CONSTRAINT IF EXISTS slot_positions_loading_source_check');
        DB::statement("ALTER TABLE slot_positions ADD CONSTRAINT slot_positions_loading_source_check CHECK (loading_source IN ('api', 'import', 'scraping', 'manual'))");
    }

    public function down(): void
    {
        DB::statement("DELETE FROM slot_positions WHERE loading_source = 'manual'");
        DB::statement('ALTER TABLE slot_positions DROP CONSTRAINT IF EXISTS slot_positions_loading_source_check');
        DB::statement("ALTER TABLE slot_positions ADD CONSTRAINT slot_positions_loading_source_check CHECK (loading_source IN ('api', 'import', 'scraping'))");
    }
};
