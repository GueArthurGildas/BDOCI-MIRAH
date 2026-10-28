<?php

namespace App\Support\Vms;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class PositionPartitions
{
    public function ensure(Carbon $month): bool
    {
        $start = $month->copy()->startOfMonth();
        $end = $start->copy()->addMonth();
        $name = 'positions_'.$start->format('Y_m');

        if (DB::selectOne('SELECT to_regclass(?) AS name', [$name])->name !== null) {
            return false;
        }

        DB::transaction(function () use ($start, $end, $name) {
            DB::statement('DROP TABLE IF EXISTS positions_rehomed');
            DB::statement(sprintf("CREATE TEMPORARY TABLE positions_rehomed AS SELECT * FROM positions_default WHERE recorded_at >= '%s' AND recorded_at < '%s'", $start->format('Y-m-d'), $end->format('Y-m-d')));
            DB::delete('DELETE FROM positions_default WHERE recorded_at >= ? AND recorded_at < ?', [$start, $end]);
            DB::statement(sprintf("CREATE TABLE %s PARTITION OF positions FOR VALUES FROM ('%s') TO ('%s')", $name, $start->format('Y-m-d'), $end->format('Y-m-d')));
            DB::statement('INSERT INTO positions SELECT * FROM positions_rehomed');
            DB::statement('DROP TABLE positions_rehomed');
        });

        return true;
    }

    public function ensureAhead(int $months): int
    {
        $created = 0;

        foreach (range(0, $months) as $offset) {
            $created += (int) $this->ensure(now()->startOfMonth()->addMonths($offset));
        }

        return $created;
    }
}
