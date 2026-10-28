<?php

namespace Database\Seeders;

use App\Enums\ExportFormat;
use App\Enums\OperationCategoryCode;
use App\Enums\ReportFormat;
use App\Enums\ReportFrequency;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Models\ScheduledReport;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Modules\Operations\Actions\CloseOperation;
use Modules\Operations\Actions\OpenOperation;
use Modules\Reports\Actions\ExportLogbook;
use Modules\Reports\Actions\ExportOperations;
use Modules\Reports\Actions\ExportVessels;
use Modules\Reports\Actions\SaveScheduledReport;
use Modules\Reports\Support\ReportCatalog;

class Phase9DemoSeeder extends Seeder
{
    private const OLD_OPERATIONS = [
        ['2019-03-12', 'Contrôle senneur Abidjan', OperationCategoryCode::Vms],
        ['2019-09-02', 'Zone interdite Sassandra', OperationCategoryCode::Inn],
        ['2020-01-20', 'Journal de pêche manquant', OperationCategoryCode::Ers],
    ];

    public function run(): void
    {
        $runner = app(ActionRunner::class);
        $director = User::where('email', 'director@example.com')->firstOrFail();
        $lead = User::role(Role::TeamLead->value)->firstOrFail();

        $this->scheduledReports($runner, $director, $lead);
        $this->oldOperations($runner, $director);
        $this->exports($runner, $director);

        Artisan::call('reports:archive-operations');
        Artisan::call('reports:run-scheduled');
    }

    private function scheduledReports(ActionRunner $runner, User $director, User $lead): void
    {
        $definitions = [
            [ReportCatalog::OPERATIONS_ACTIVITY, ReportFrequency::Monthly, ReportFormat::Pdf, $director, true],
            [ReportCatalog::LOGBOOK_ACTIVITY, ReportFrequency::Weekly, ReportFormat::Excel, $lead, true],
            [ReportCatalog::OPERATIONS_ACTIVITY, ReportFrequency::Yearly, ReportFormat::Pdf, $director, false],
        ];

        foreach ($definitions as [$code, $frequency, $format, $recipient, $active]) {
            if (ScheduledReport::where(['code' => $code, 'frequency' => $frequency->value, 'recipient_id' => $recipient->id])->exists()) {
                continue;
            }

            $runner->run(SaveScheduledReport::class, $director, [
                'code' => $code,
                'frequency' => $frequency->value,
                'format' => $format->value,
                'recipient_id' => $recipient->id,
                'is_active' => $active,
            ]);
        }
    }

    private function oldOperations(ActionRunner $runner, User $director): void
    {
        $vessels = Vessel::query()->where('category', 'fishing')->orderBy('id')->limit(count(self::OLD_OPERATIONS))->get();

        foreach (self::OLD_OPERATIONS as $index => [$startedOn, $name, $category]) {
            $operation = $runner->run(OpenOperation::class, $director, [
                'category_id' => OperationCategory::where('code', $category)->firstOrFail()->id,
                'name' => $name,
                'started_at' => "{$startedOn} 08:00:00",
                'origin' => 'CSP',
                'summary' => 'Opération ancienne conservée pour la démonstration de l\'archivage.',
                'vessel_ids' => $vessels->has($index) ? [$vessels[$index]->id] : [],
            ]);

            $runner->run(CloseOperation::class, $director, ['operation_id' => $operation->id, 'result' => 'Dossier traité et clos.']);

            Operation::whereKey($operation->id)->update(['ended_at' => "{$startedOn} 18:00:00"]);
        }
    }

    private function exports(ActionRunner $runner, User $director): void
    {
        $runner->run(ExportOperations::class, $director, ['format' => ExportFormat::Csv->value, 'year' => now()->year]);
        $runner->run(ExportVessels::class, $director, ['format' => ExportFormat::Pdf->value, 'search' => 'A']);
        $runner->run(ExportLogbook::class, $director, ['format' => ExportFormat::Csv->value, 'from' => now()->subDays(7)->toDateString()]);
    }
}
