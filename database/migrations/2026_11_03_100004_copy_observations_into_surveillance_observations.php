<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            INSERT INTO surveillance_observations (vessel_id, body, status, created_by, created_at, legacy_observation_id)
            SELECT vessel_id,
                   description,
                   CASE WHEN status = 'closed' THEN 'closed' ELSE 'monitoring' END,
                   author_id,
                   observed_at,
                   id
            FROM observations
            WHERE vessel_id IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM surveillance_observations s WHERE s.legacy_observation_id = observations.id)
        SQL);
    }

    public function down(): void
    {
        DB::table('surveillance_observations')->whereNotNull('legacy_observation_id')->delete();
    }
};
