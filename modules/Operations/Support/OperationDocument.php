<?php

namespace Modules\Operations\Support;

use App\Enums\ActorRole;
use App\Enums\IdentifierType;
use App\Enums\LogbookDirection;
use App\Enums\OperationCategoryCode;
use App\Enums\OperationMilestone;
use App\Models\LogbookEntry;
use App\Models\Operation;
use App\Models\OperationEvent;
use App\Models\Vessel;
use App\Models\VesselActor;
use App\Support\Documents\DocumentStore;
use App\Support\Operations\WeatherScales;
use App\Support\Printing\PrintedDocument;
use App\Support\Printing\TemplatePalette as Palette;
use Illuminate\Support\Collection;
use Modules\Operations\Actions\OpenOperation;

final class OperationDocument
{
    private const CONTEXT_WIDTHS = [18, 16, 17, 16, 17, 16];

    private const ENTRY_WIDTHS = [15, 17, 68];

    private const CLOSING_WIDTHS = [24, 26, 24, 26];

    private const BLANK_ROWS = 3;

    public function __construct(private readonly DocumentStore $documents, private readonly WeatherScales $scales) {}

    public static function basename(Operation $operation): string
    {
        return 'Main_courante_'.str_replace('-', '_', $operation->code);
    }

    public function build(Operation $operation): PrintedDocument
    {
        $operation->loadMissing([
            'category', 'context', 'personnel', 'creator', 'events.channel', 'logbookEntries.channel',
            'vessels.flagCountry', 'vessels.vesselType', 'vessels.activeIdentifiers', 'vessels.currentLicense.gears',
            'vessels.actors.person.nationality', 'vessels.actors.legalEntity',
        ]);

        $document = new PrintedDocument(
            self::basename($operation),
            ['République de Côte d\'Ivoire', 'Centre de Surveillance des Pêches', __('operations::operations.print.file_title')],
            __('logbook::logbook.print.daily_title'),
        );

        $document->table([100], [[$operation->isFinished()
            ? PrintedDocument::banner(__('operations::operations.print.status_closed', ['date' => $operation->ended_at?->format('d/m/Y H:i') ?? '—']), Palette::CLOSED_GREEN, 1)
            : PrintedDocument::banner(__('operations::operations.print.status_open', ['date' => now()->format('d/m/Y H:i')]), Palette::OPENED_RED, 1)]]);

        $this->addContext($document, $operation);
        $this->addLogbook($document, $operation);
        $this->addClosing($document, $operation);

        $attachments = $this->documents->for($operation)->filter(fn ($file) => $file->name !== self::basename($operation).'.pdf')->pluck('name')->values()->all();
        $document->paragraph(__('operations::operations.print.attachments'), true);
        $document->bullets($attachments === [] ? [__('operations::operations.print.no_attachment')] : $attachments);

        return $document;
    }

    private function addContext(PrintedDocument $document, Operation $operation): void
    {
        $context = $operation->context;
        $label = fn (string $text, int $span = 1, string $fill = Palette::LABEL_BLUE_ALT) => PrintedDocument::cell($text, ['span' => $span, 'fill' => $fill]);
        $value = fn (mixed $text, int $span = 1) => PrintedDocument::cell($text === null ? null : (string) $text, ['span' => $span]);
        $scale = fn (string $scale, ?string $key) => $key === null ? null : ($this->scales->options($scale)[$key] ?? $key);
        $field = fn (string $key) => __('operations::operations.fields.'.$key);

        $rows = [
            [PrintedDocument::banner(__('operations::operations.context_title'), Palette::CONTEXT_BLUE, 6)],
            [
                $label(__('operations::operations.print.type_crossed')),
                ...array_map(fn (OperationCategoryCode $code) => PrintedDocument::cell(strtoupper($code->value), [
                    'bold' => $code === $operation->category->code,
                    'strike' => $code !== $operation->category->code,
                ]), OperationCategoryCode::cases()),
            ],
            [$label($field('number')), $value($operation->code, 5)],
            [$label(__('operations::operations.print.first_event'), 6)],
            [$label(__('logbook::logbook.handovers.fields.date')), $value($operation->started_at->format('d/m/Y'), 2), $label(__('operations::operations.print.time_utc')), $value($operation->started_at->format('H:i'), 2)],
        ];

        if ($context?->first_event) {
            $rows[] = [$value($context->first_event, 6)];
        }

        $rows = [
            ...$rows,
            [PrintedDocument::banner(__('operations::operations.weather_title'), Palette::TITLE_BLUE, 6)],
            [$label($field('temperature')), $value($context?->temperature), $label($field('pressure')), $value($context?->pressure), $label($field('visibility')), $value($scale(WeatherScales::VISIBILITY, $context?->visibility))],
            [$label($field('cloud_cover')), $value($scale(WeatherScales::CLOUD_COVER, $context?->cloud_cover)), $label($field('wind_beaufort')), $value($context?->wind_beaufort), $label($field('sea_state')), $value($scale(WeatherScales::SEA_STATE, $context?->sea_state))],
            [$label($field('weather_notes'), 1, ''), $value($context?->weather_notes, 5)],
        ];

        foreach ($operation->vessels->isEmpty() ? [null] : $operation->vessels as $vessel) {
            $rows = [...$rows, ...$this->vesselRows($vessel)];
        }

        $comments = collect([
            $context?->comments,
            $context?->vessel_position ? $field('vessel_position').' : '.$context->vessel_position : null,
            $context?->vessel_activity ? $field('vessel_activity').' : '.$context->vessel_activity : null,
        ])->filter()->join("\n");

        $manager = $operation->personnel->first(fn ($user) => $user->pivot->role === OpenOperation::MANAGER_ROLE);
        $operator = $operation->personnel->first(fn ($user) => $user->pivot->role === OpenOperation::LEAD_ROLE) ?? $operation->creator;

        $rows = [
            ...$rows,
            [PrintedDocument::cell($field('comments'), ['span' => 6, 'fill' => Palette::VESSEL_SECTION])],
            [$value($comments, 6)],
            [PrintedDocument::banner(__('operations::operations.personnel_title'), Palette::TITLE_BLUE, 6)],
            [$label($field('supervised_by'), 2, ''), $value($operation->csp_manager_name ?? $manager?->fullName(), 4)],
            [$label($field('operator'), 2, ''), $value($operator?->fullName(), 4)],
        ];

        $document->table(self::CONTEXT_WIDTHS, $rows);
    }

    /** @return array<int, array<int, array<string, mixed>>> */
    private function vesselRows(?Vessel $vessel): array
    {
        $label = fn (string $key) => PrintedDocument::cell(__('operations::operations.fields.'.$key), ['fill' => Palette::VESSEL_LABEL]);
        $value = fn (mixed $text, int $span = 2) => PrintedDocument::cell($text === null || $text === '' ? null : (string) $text, ['span' => $span]);
        $section = fn (string $key) => [PrintedDocument::cell(__('operations::operations.fields.'.$key), ['span' => 6, 'fill' => Palette::VESSEL_SECTION])];
        $actor = fn (array $roles) => $vessel?->actors->first(fn (VesselActor $actor) => $actor->ended_on === null && in_array($actor->role, $roles, true));
        $holder = fn (?VesselActor $actor) => $actor?->legalEntity ?? $actor?->person;

        $owner = $holder($actor([ActorRole::Shipowner, ActorRole::Owner]));
        $agent = $holder($actor([ActorRole::LocalAgent]));
        $captainActor = $actor([ActorRole::Captain, ActorRole::FishingCaptain]);
        $captain = $captainActor?->person;
        $name = fn ($party) => $party ? trim(($party->first_name ?? '').' '.$party->name) : ($captainActor?->holderName());

        $rows = [
            [PrintedDocument::banner(__('operations::operations.vessel_title'), Palette::VESSEL_ORANGE, 6)],
            [$label('vessel_name'), $value($vessel?->name), $label('flag'), $value($vessel?->flagName())],
            [$label('imo'), $value($vessel ? ($vessel->imo_number ?? $vessel->identifierValue(IdentifierType::Imo)) : null), $label('mmsi'), $value($vessel?->identifierValue(IdentifierType::Mmsi))],
            [$label('registration'), $value($vessel?->registration_number), $label('call_sign'), $value($vessel ? ($vessel->call_sign ?? $vessel->identifierValue(IdentifierType::Ircs)) : null)],
            [$label('vessel_type'), $value($vessel?->vesselType?->label ?? ($vessel?->type ? __($vessel->type->label()) : null)), $label('gear'), $value($vessel?->currentLicense?->gears->pluck('label')->join(', '))],
            [$label('length'), $value($vessel ? ($vessel->length_m ?? $vessel->length_meters) : null), $label('beam'), $value($vessel?->beam_m)],
            [$label('draught'), $value(null), $label('tonnage'), $value($vessel?->gross_tonnage)],
        ];

        foreach (['owner' => $owner, 'local_agent' => $agent] as $role => $party) {
            $rows[] = $section($role);

            foreach (['contact_name' => $party?->name, 'address' => $party?->address, 'phone' => $party?->phone, 'email' => $party?->email] as $key => $text) {
                $rows[] = [$label($key), $value($key === 'contact_name' ? $name($party) : $text, 5)];
            }
        }

        $rows[] = $section('captain');
        $rows[] = [$label('full_name'), $value($captain ? $name($captain) : $captainActor?->holderName(), 5)];
        $rows[] = [$label('nationality'), $value($captain?->nationality?->label, 5)];
        $rows[] = [$label('email'), $value($captain?->email, 5)];

        return $rows;
    }

    private function addLogbook(PrintedDocument $document, Operation $operation): void
    {
        $header = fn (string $text) => PrintedDocument::cell($text, ['fill' => Palette::COLUMN_BLUE, 'bold' => true]);
        $rows = [
            [PrintedDocument::banner(__('operations::operations.print.logbook_title'), Palette::TITLE_BLUE, 3)],
            [$header(__('logbook::logbook.print.when')), $header(__('logbook::logbook.print.how')), $header(__('logbook::logbook.print.what'))],
        ];

        $previousDate = null;

        foreach ($this->lines($operation) as $line) {
            $date = $line['at']->format('Ymd');
            $rows[] = [
                PrintedDocument::cell(($date !== $previousDate ? $date."\n" : '').$line['at']->format('H:i')),
                PrintedDocument::cell($line['how']),
                PrintedDocument::cell($line['what']),
            ];
            $previousDate = $date;
        }

        for ($blank = $previousDate === null ? self::BLANK_ROWS : 0; $blank > 0; $blank--) {
            $rows[] = [PrintedDocument::cell(''), PrintedDocument::cell(''), PrintedDocument::cell('')];
        }

        $document->table(self::ENTRY_WIDTHS, $rows);
    }

    /** @return Collection<int, array{at: \Illuminate\Support\Carbon, how: string, what: array<int, array<string, mixed>>}> */
    private function lines(Operation $operation): Collection
    {
        $how = fn ($channel, ?LogbookDirection $direction) => ($channel?->label ?? '').($direction === LogbookDirection::Sent ? ' ('.mb_strtolower(__($direction->label())).')' : '');

        $milestones = $operation->logbookEntries
            ->filter(fn (LogbookEntry $entry) => $entry->operation_milestone !== null)
            ->map(fn (LogbookEntry $entry) => [
                'at' => $entry->occurred_at,
                'order' => $entry->operation_milestone === OperationMilestone::Opened ? 0 : 2,
                'how' => $how($entry->channel, $entry->direction),
                'what' => [
                    ['text' => $entry->content],
                    ['text' => __('logbook::logbook.entries.operation_'.$entry->operation_milestone->value, ['code' => $operation->code])],
                ],
            ]);

        $events = $operation->events->map(fn (OperationEvent $event) => [
            'at' => $event->occurred_at,
            'order' => 1,
            'how' => $how($event->channel, $event->direction),
            'what' => array_values(array_filter([
                ['text' => $event->body],
                $event->is_order_or_decision ? ['text' => __('logbook::logbook.entries.order_or_decision'), 'bold' => true] : null,
            ])),
        ]);

        return $milestones->concat($events)->sortBy([['at', 'asc'], ['order', 'asc']])->values();
    }

    private function addClosing(PrintedDocument $document, Operation $operation): void
    {
        $closed = $operation->isFinished();
        $end = $closed ? $operation->ended_at : null;
        $minutes = $end ? (int) $operation->started_at->diffInMinutes($end) : null;
        $field = fn (string $key) => __('operations::operations.fields.'.$key);
        $label = fn (string $text, int $span = 1) => PrintedDocument::cell($text, ['span' => $span]);
        $value = fn (?string $text, int $span = 1) => PrintedDocument::cell($text, ['span' => $span]);

        $rows = [
            [PrintedDocument::banner(__('operations::operations.closing_title'), Palette::OPERATION_GREEN, 4)],
            [$label(__('operations::operations.print.start_date')), $value($operation->started_at->format('d/m/Y')), $label(__('operations::operations.print.start_time')), $value($operation->started_at->format('H:i'))],
            [$label(__('operations::operations.print.end_date')), $value($end?->format('d/m/Y')), $label(__('operations::operations.print.end_time')), $value($end?->format('H:i'))],
            [$label($field('duration'), 2), $value($minutes === null ? null : __('operations::operations.duration', ['days' => intdiv($minutes, 1440), 'hours' => intdiv($minutes % 1440, 60), 'minutes' => $minutes % 60]), 2)],
            [PrintedDocument::banner(__('operations::operations.reminder_title'), Palette::OPERATION_GREEN, 4)],
            [$label($field('type')), $value(strtoupper($operation->category->code->value)), $label(__('operations::operations.print.number_short')), $value(str_pad((string) $operation->number, 3, '0', STR_PAD_LEFT))],
            [$label($field('vessel'), 2), $value($operation->vessels->pluck('name')->join(', '), 2)],
            [$label(__('operations::operations.print.summary')), $value($operation->summary, 3)],
            [PrintedDocument::banner($field('result'), Palette::OPERATION_GREEN, 4)],
            [$value($closed ? $operation->result : null, 4)],
            [PrintedDocument::banner($field('infractions_noted'), Palette::OPERATION_GREEN, 4)],
            [$value($closed ? $operation->infractions_noted : null, 4)],
        ];

        $document->table(self::CLOSING_WIDTHS, $rows);
    }
}
