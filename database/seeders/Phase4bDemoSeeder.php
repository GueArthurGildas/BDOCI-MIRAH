<?php

namespace Database\Seeders;

use App\Enums\EezDeclarationType;
use App\Enums\EezReason;
use App\Enums\EntryExitExplanation;
use App\Models\EntryExitLink;
use App\Models\ReferenceList;
use App\Models\ReferenceListValue;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Database\Seeder;
use Modules\Movements\Actions\AcknowledgeEezDeclaration;
use Modules\Movements\Actions\RecordEezDeclaration;
use Modules\Movements\Http\Controllers\EezDeclarationController;
use Modules\Movements\Services\MissedDeclarationDetector;

class Phase4bDemoSeeder extends Seeder
{
    private const CHANNELS = [
        ['email', 'E-mail du capitaine'],
        ['agent_email', 'E-mail de l\'agent maritime'],
        ['radio', 'Radio VHF'],
        ['phone', 'Téléphone'],
        ['portal', 'Interface de déclaration'],
    ];

    private const TRIPS = [
        ['exit' => true, 'reason' => EezReason::EezFishing, 'entry_kg' => [['YFT', 18000], ['SKJ', 22000]], 'exit_kg' => [['YFT', 18500], ['SKJ', 21800]], 'destination' => 'Abidjan'],
        ['exit' => true, 'reason' => EezReason::EezFishing, 'entry_kg' => [['BET', 9000]], 'exit_kg' => [['BET', 14500]], 'destination' => 'Abidjan'],
        ['exit' => true, 'reason' => EezReason::EezTransit, 'entry_kg' => [], 'exit_kg' => [], 'destination' => 'Dakar'],
        ['exit' => true, 'reason' => EezReason::IvorianPortTransit, 'entry_kg' => [['SAA', 12000]], 'exit_kg' => [['SAA', 11800]], 'destination' => 'San Pedro'],
        ['exit' => false, 'reason' => EezReason::EezFishing, 'entry_kg' => [['SWO', 3000]], 'exit_kg' => [], 'destination' => 'Abidjan'],
        ['exit' => false, 'reason' => EezReason::HighSeasFishing, 'entry_kg' => [], 'exit_kg' => [], 'destination' => 'Sassandra'],
    ];

    public function run(): void
    {
        $channels = $this->seedChannels();
        $actor = User::where('email', 'director@example.com')->firstOrFail();
        $runner = app(ActionRunner::class);
        $species = Species::pluck('id', 'fao_code');
        $vessels = Vessel::whereHas('flagCountry', fn ($query) => $query->where('iso2_code', '!=', 'CI'))->where('category', 'fishing')->orderBy('id')->limit(count(self::TRIPS))->get();

        foreach (self::TRIPS as $index => $trip) {
            $vessel = $vessels[$index];
            $entryAt = now()->subDays(6 - $index)->subHours(5);

            $entry = $runner->run(RecordEezDeclaration::class, $actor, $this->payload($vessel, EezDeclarationType::Entry, $entryAt, $trip, $trip['entry_kg'], $species, $channels, $index));
            $runner->run(AcknowledgeEezDeclaration::class, $actor, ['eez_declaration_id' => $entry->id]);

            if ($trip['exit']) {
                $exit = $runner->run(RecordEezDeclaration::class, $actor, $this->payload($vessel, EezDeclarationType::Exit, $entryAt->copy()->addDays(3), $trip, $trip['exit_kg'], $species, $channels, $index));

                if ($index === 0) {
                    EntryExitLink::where('exit_declaration_id', $exit->id)->update(['explanation' => EntryExitExplanation::CatchDocument]);
                }
            }
        }

        $this->seedUnidentifiedDeclaration($runner, $actor, $channels);

        app(MissedDeclarationDetector::class)->detect();
    }

    private function seedChannels(): array
    {
        $list = ReferenceList::firstOrCreate(['code' => EezDeclarationController::RECEPTION_CHANNELS_LIST], ['label' => 'Moyens de réception des déclarations ZEE']);

        return collect(self::CHANNELS)->values()->map(fn (array $channel, int $order) => ReferenceListValue::firstOrCreate(
            ['reference_list_id' => $list->id, 'code' => $channel[0]],
            ['label' => $channel[1], 'sort_order' => $order, 'is_active' => true],
        ))->pluck('id')->all();
    }

    private function payload(Vessel $vessel, EezDeclarationType $type, $at, array $trip, array $catches, $species, array $channels, int $index): array
    {
        return [
            'vessel_id' => $vessel->id,
            'type' => $type,
            'received_at' => $at,
            'reception_channel_id' => $channels[$index % count($channels)],
            'crossed_at' => $at->copy()->subHours(2),
            'latitude' => 3.4 + $index * 0.15,
            'longitude' => -5.2 + $index * 0.2,
            'route' => $type === EezDeclarationType::Entry ? 'Nord-est' : 'Sud-ouest',
            'speed' => 8.5,
            'destination' => $trip['destination'],
            'reason' => $trip['reason'],
            'declared_name' => $vessel->name,
            'declared_imo' => $vessel->imo_number,
            'declared_ircs' => $vessel->call_sign,
            'declared_flag' => $vessel->flag,
            'declared_owner' => $vessel->owner,
            'catches' => collect($catches)->map(fn (array $catch) => ['species_id' => $species[$catch[0]], 'weight_kg' => $catch[1]])->all(),
        ];
    }

    private function seedUnidentifiedDeclaration(ActionRunner $runner, User $actor, array $channels): void
    {
        $runner->run(RecordEezDeclaration::class, $actor, [
            'type' => EezDeclarationType::Entry,
            'received_at' => now()->subHours(3),
            'reception_channel_id' => $channels[0],
            'reason' => EezReason::EezTransit,
            'destination' => 'Tema',
            'declared_name' => 'ATLANTIC DAWN',
            'declared_flag' => 'Ghana',
            'declared_owner' => 'Tema Offshore Fisheries',
        ]);
    }
}
