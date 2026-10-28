<?php

namespace Modules\Tracking\Console;

use App\Enums\AlertRuleType;
use App\Models\Alert;
use App\Models\Vessel;
use App\Support\Vms\EmissionRate;
use App\Support\Vms\MonitoredVessels;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AggregateMonthlyEmission extends Command
{
    protected $signature = 'tracking:aggregate-monthly {--month= : Month to aggregate as YYYY-MM (default: previous month)}';

    protected $description = 'Compute the monthly emission rate and silence count of monitored vessels';

    public function handle(EmissionRate $emission): int
    {
        $from = $this->option('month') ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth() : now()->subMonthNoOverflow()->startOfMonth();
        $to = $from->copy()->addMonth();

        Vessel::whereIn('id', MonitoredVessels::ids())->get()->each(function (Vessel $vessel) use ($emission, $from, $to) {
            DB::table('monthly_aggregates')->upsert([[
                'year_month' => $from->format('Y-m'),
                'vessel_id' => $vessel->id,
                'emission_rate' => $emission->forVessel($vessel, $from, $to)['rate'],
                'silences_count' => Alert::where('vessel_id', $vessel->id)->where('type', AlertRuleType::Silence)->where('triggered_at', '>=', $from)->where('triggered_at', '<', $to)->count(),
            ]], ['year_month', 'vessel_id'], ['emission_rate', 'silences_count']);
        });

        return self::SUCCESS;
    }
}
