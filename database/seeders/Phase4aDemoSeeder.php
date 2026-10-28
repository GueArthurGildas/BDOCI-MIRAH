<?php

namespace Database\Seeders;

use App\Enums\FnpDeregistrationReason;
use App\Enums\LicenseRecordStatus;
use App\Enums\SuspensionReason;
use App\Models\Gear;
use App\Models\Institution;
use App\Models\License;
use App\Models\LicenseSuspension;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use Illuminate\Database\Seeder;
use Modules\Licenses\Actions\GenerateLicenseRegister;
use Modules\Licenses\Actions\ImportLicenseFile;
use Modules\Licenses\Actions\PublishAuthorizedList;
use Modules\Licenses\Actions\RecordExternalAuthorization;
use Modules\Licenses\Actions\RecordVmsCertificate;
use Modules\Licenses\Actions\RevokeLicense;
use Modules\Licenses\Actions\SetFnpStatus;
use Modules\Licenses\Actions\StartLicenseBatch;
use Modules\Licenses\Actions\ValidateLicenseImport;

class Phase4aDemoSeeder extends Seeder
{
    private const CONDITIONS = [
        'Embarquement d\'un observateur à bord sur demande du CSP',
        'Interdiction de pêche dans la bande des 12 milles',
        'Déclaration d\'entrée et de sortie de la ZEE obligatoire',
        'Débarquement obligatoire au port d\'Abidjan ou de San Pedro',
    ];

    public function run(): void
    {
        $director = User::where('email', 'director@example.com')->firstOrFail();
        $runner = app(ActionRunner::class);

        $this->enrichLicenses();
        $this->seedSuspensions($director);
        $this->seedRevocation($runner, $director);
        $this->seedFnp($runner, $director);
        $this->seedAuthorizations($runner, $director);
        $this->seedImports($runner, $director);
        $runner->run(GenerateLicenseRegister::class, $director, ['year' => now()->year - 1]);
        $runner->run(PublishAuthorizedList::class, $director);
    }

    private function enrichLicenses(): void
    {
        $zones = Zone::pluck('id');
        $gears = Gear::pluck('id');
        $species = Species::pluck('id');

        License::orderBy('id')->get()->each(function (License $license, int $index) use ($zones, $gears, $species) {
            $license->zones()->sync($zones->random(min(2, $zones->count()))->all());
            $license->gears()->sync($gears->random(min(2, $gears->count()))->all());
            $license->species()->sync($species->random(min(3, $species->count()))->all());
            $license->conditions()->create(['body' => self::CONDITIONS[$index % count(self::CONDITIONS)]]);
        });
    }

    private function seedSuspensions(User $director): void
    {
        License::where('status', LicenseRecordStatus::Suspended->value)->orderBy('id')->get()->each(function (License $license, int $index) use ($director) {
            LicenseSuspension::create([
                'license_id' => $license->id,
                'reason' => $index === 0 ? SuspensionReason::Art105 : SuspensionReason::ForceMajeure,
                'started_on' => today()->subWeeks(2),
                'planned_end_on' => $index === 0 ? today()->addWeeks(8) : null,
                'decided_by' => $director->id,
            ]);
        });
    }

    private function seedRevocation(ActionRunner $runner, User $director): void
    {
        $license = License::where('status', LicenseRecordStatus::Active->value)->where('expires_on', '>', today()->addDays(120))->orderBy('id')->first();

        if ($license) {
            $runner->run(RevokeLicense::class, $director, [
                'license_id' => $license->id,
                'reason' => 'Pêche dans la zone d\'exclusion sans autorisation',
                'revoked_on' => today()->subDays(5)->toDateString(),
                'is_repeat_offense' => true,
            ]);
        }
    }

    private function seedFnp(ActionRunner $runner, User $director): void
    {
        $vessels = Vessel::where('category', 'fishing')->orderBy('id')->limit(30)->get();

        foreach ($vessels as $index => $vessel) {
            $deregistered = $index === 3 || $index === 7;

            $runner->run(SetFnpStatus::class, $director, [
                'vessel_id' => $vessel->id,
                'status' => $deregistered ? 'deregistered' : 'registered',
                'deregistration_reason' => $deregistered ? ($index === 3 ? FnpDeregistrationReason::FlagChange->value : FnpDeregistrationReason::FiveYearsWithoutLicense->value) : null,
                'status_on' => today()->subMonths($deregistered ? 1 : 18)->toDateString(),
            ]);

            if ($index % 2 === 0) {
                $runner->run(RecordVmsCertificate::class, $director, [
                    'vessel_id' => $vessel->id,
                    'issued_on' => today()->subMonths(11)->addDays($index * 3)->toDateString(),
                    'expires_on' => today()->addMonth()->addDays($index * 3)->toDateString(),
                ]);
            }
        }
    }

    private function seedAuthorizations(ActionRunner $runner, User $director): void
    {
        $institutions = Institution::orderBy('id')->limit(2)->get();

        if ($institutions->isEmpty()) {
            return;
        }

        Vessel::where('category', 'fishing')->orderBy('id')->limit(4)->get()->each(fn (Vessel $vessel, int $index) => $runner->run(RecordExternalAuthorization::class, $director, [
            'vessel_id' => $vessel->id,
            'institution_id' => $institutions[$index % $institutions->count()]->id,
            'number' => sprintf('EXT-%04d-%02d', now()->year, $index + 1),
            'valid_from' => today()->subMonths(2)->toDateString(),
            'valid_to' => today()->addMonths(10)->toDateString(),
        ]));
    }

    private function seedImports(ActionRunner $runner, User $director): void
    {
        $vessels = Vessel::where('category', 'fishing')->whereDoesntHave('licenses')->whereNotNull('registration_number')->orderBy('id')->get();
        $zone = Zone::orderBy('id')->value('name');
        $gear = Gear::orderBy('id')->value('code_fao');
        $species = Species::orderBy('id')->value('fao_code');
        $header = 'registration_number;number;segment;issued_on;expires_on;zones;gears;species;conditions';

        $row = fn (?Vessel $vessel, string $number, string $registration = '') => implode(';', [
            $vessel?->registration_number ?? $registration, $number, 'semi_industrial', today()->subMonth()->toDateString(), today()->addMonths(11)->toDateString(), $zone, $gear, $species, 'Déclaration ZEE obligatoire',
        ]);

        if ($vessels->count() < 5) {
            return;
        }

        $validated = $runner->run(ImportLicenseFile::class, $director, ['document_id' => $this->csv('licences-2026-lot1.csv', [$header, $row($vessels[0], 'CI-IMP-2026-001'), $row($vessels[1], 'CI-IMP-2026-002')])]);
        $runner->run(ValidateLicenseImport::class, $director, ['import_id' => $validated->id]);

        $runner->run(ImportLicenseFile::class, $director, ['document_id' => $this->csv('licences-2026-lot2.csv', [
            $header,
            $row($vessels[2], 'CI-IMP-2026-003'),
            $row($vessels[3], 'CI-IMP-2026-004'),
            $row(null, 'CI-IMP-2026-005', 'UNKNOWN-REG'),
            $row($vessels[4], 'CI-IMP-2026-001'),
        ])]);

        if ($vessels->count() >= 7) {
            $runner->run(StartLicenseBatch::class, $director, ['vessel_ids' => [$vessels[5]->id, $vessels[6]->id]]);
        }
    }

    /** @param array<int, string> $lines */
    private function csv(string $name, array $lines): int
    {
        return app(DocumentStore::class)->storeContent($name, implode("\n", $lines), 'text/csv')->id;
    }
}
