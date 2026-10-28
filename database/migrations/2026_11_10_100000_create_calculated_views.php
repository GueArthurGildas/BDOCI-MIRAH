<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const VIEWS = [
        'v_authorized_vessels' => <<<'SQL'
            SELECT v.id AS vessel_id, v.name, l.number, l.expires_on
            FROM vessels v JOIN licenses l ON l.vessel_id = v.id
            WHERE l.status = 'active' AND l.expires_on >= CURRENT_DATE AND v.deleted_at IS NULL
            SQL,
        'v_morning_board' => <<<'SQL'
            SELECT w.id, w.vessel_id, v.name, w.origin, w.started_at, (CURRENT_DATE - w.started_at::date) AS age_days
            FROM watch_entries w JOIN vessels v ON v.id = w.vessel_id
            WHERE w.status = 'open'
            SQL,
        'v_expiring_licenses' => <<<'SQL'
            SELECT l.id, l.vessel_id, l.expires_on, (l.expires_on - CURRENT_DATE) AS days_left
            FROM licenses l
            WHERE l.status = 'active' AND l.expires_on >= CURRENT_DATE AND l.expires_on <= CURRENT_DATE + 90
            SQL,
        'v_vessel_timeline' => <<<'SQL'
            SELECT e.vessel_id, e.occurred_at, m.code AS module, e.type, e.summary_key, e.summary_params
            FROM vessel_events e JOIN module_registry m ON m.id = e.module_id
            SQL,
    ];

    public function up(): void
    {
        foreach (self::VIEWS as $name => $definition) {
            DB::statement("CREATE OR REPLACE VIEW {$name} AS {$definition}");
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::VIEWS) as $name) {
            DB::statement("DROP VIEW IF EXISTS {$name}");
        }
    }
};
