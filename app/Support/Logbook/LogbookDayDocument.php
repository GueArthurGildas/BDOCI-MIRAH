<?php

namespace App\Support\Logbook;

use App\Enums\LogbookDirection;
use App\Enums\OperationCategoryCode;
use App\Enums\OperationMilestone;
use App\Models\LogbookDay;
use App\Models\LogbookEntry;
use App\Models\ShiftHandover;
use App\Models\User;
use App\Support\Printing\PrintedDocument;
use App\Support\Printing\TemplatePalette as Palette;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class LogbookDayDocument
{
    private const CARTOUCHE_WIDTHS = [24, 26, 24, 26];

    private const ENTRY_WIDTHS = [15, 17, 68];

    private const BLANK_ROWS = 3;

    public static function basename(LogbookDay $day): string
    {
        return 'Main_courante_CSP_du_'.$day->day->format('Y_m_d');
    }

    /** @param array<string, mixed>|null $counters */
    public function build(LogbookDay $day, ?array $counters = null, ?string $closingInstructions = null, ?User $closer = null, ?Carbon $closedAt = null): PrintedDocument
    {
        $day->loadMissing(['entries.channel', 'entries.vessels', 'entries.operation', 'entries.corrects', 'entries.observation', 'entries.surveillanceObservation', 'entries.witnessReport', 'entries.canceller', 'handovers.outgoing', 'handovers.incoming', 'handovers.supervisor', 'closing.closer']);

        if ($counters === null && $day->closing) {
            $counters = $day->closing->counters;
            $closingInstructions = $day->closing->instructions;
            $closer = $day->closing->closer;
            $closedAt = $day->closing->closed_at;
        }

        $document = new PrintedDocument(
            self::basename($day),
            ['République de Côte d\'Ivoire', 'Centre de Surveillance des Pêches', __('logbook::logbook.print.daily_title')],
            __('logbook::logbook.print.daily_title'),
        );

        $document->table([100], [[$closedAt === null
            ? PrintedDocument::banner(__('logbook::logbook.print.status_open', ['date' => now()->format('d/m/Y H:i')]), Palette::OPENED_RED, 1)
            : PrintedDocument::banner(__('logbook::logbook.print.status_closed', ['date' => $closedAt->format('d/m/Y H:i'), 'name' => $closer?->fullName() ?? '—']), Palette::CLOSED_GREEN, 1)]]);

        $entries = $day->entries->sortBy([['occurred_at', 'asc'], ['id', 'asc']])->values();
        $handovers = $day->handovers->sortBy([['handed_over_at', 'asc'], ['id', 'asc']])->values();

        if ($handovers->isEmpty()) {
            $this->addHandover($document, null);
            $this->addEntries($document, $day, $entries);
        } else {
            $before = $entries->filter(fn (LogbookEntry $entry) => $entry->occurred_at->lt($handovers->first()->handed_over_at));

            if ($before->isNotEmpty()) {
                $this->addEntries($document, $day, $before->values());
            }

            foreach ($handovers as $index => $handover) {
                $next = $handovers->get($index + 1);
                $this->addHandover($document, $handover);
                $this->addEntries($document, $day, $entries->filter(fn (LogbookEntry $entry) => $entry->occurred_at->gte($handover->handed_over_at) && ($next === null || $entry->occurred_at->lt($next->handed_over_at)))->values());
            }
        }

        $this->addClosing($document, $counters, $closingInstructions, $closer, $closedAt);

        return $document;
    }

    private function addHandover(PrintedDocument $document, ?ShiftHandover $handover): void
    {
        $label = fn (string $text, int $span = 1, string $fill = Palette::LABEL_BLUE) => PrintedDocument::cell($text, ['span' => $span, 'fill' => $fill]);
        $value = fn (?string $text, int $span = 1) => PrintedDocument::cell($text, ['span' => $span]);
        $counters = $handover?->operation_counters ?? [];
        $open = $handover?->open_operations ?? [];

        $rows = [
            [PrintedDocument::banner(__('logbook::logbook.handovers.title'), Palette::TITLE_BLUE, 4)],
            [$label(__('logbook::logbook.handovers.fields.date')), $value($handover?->handed_over_at->format('d/m/Y')), $label(__('logbook::logbook.handovers.fields.time')), $value($handover?->handed_over_at->format('H:i'))],
            [$label(__('logbook::logbook.handovers.fields.supervisor')), $value($handover?->supervisor_name ?? $handover?->supervisor?->fullName(), 3)],
            [$label(__('logbook::logbook.handovers.fields.outgoing')), $value($handover?->outgoing?->fullName()), $label(__('logbook::logbook.handovers.fields.incoming')), $value($handover?->incoming?->fullName())],
            ...$this->counterRows($counters, Palette::LABEL_BLUE, Palette::LABEL_BLUE_ALT, $handover !== null),
            ...$this->openOperationRows($open, null, $handover !== null),
            [$label(__('logbook::logbook.handovers.fields.instructions'), 4)],
            ...$this->textRows($handover?->instructions),
        ];

        $document->table(self::CARTOUCHE_WIDTHS, $rows);
    }

    /** @param Collection<int, LogbookEntry> $entries */
    private function addEntries(PrintedDocument $document, LogbookDay $day, Collection $entries): void
    {
        $header = fn (string $text) => PrintedDocument::cell($text, ['fill' => Palette::COLUMN_BLUE, 'bold' => true]);
        $rows = [[$header(__('logbook::logbook.print.when')), $header(__('logbook::logbook.print.how')), $header(__('logbook::logbook.print.what'))]];

        foreach ($entries as $entry) {
            $rows[] = [
                PrintedDocument::cell(($entry->occurred_at->isSameDay($day->day) ? '' : $entry->occurred_at->format('Ymd')."\n").$entry->occurred_at->format('H:i')),
                PrintedDocument::cell($entry->channel->label.($entry->direction === LogbookDirection::Sent ? ' ('.mb_strtolower(__($entry->direction->label())).')' : '')),
                PrintedDocument::cell($this->what($entry)),
            ];
        }

        for ($blank = $entries->isEmpty() ? self::BLANK_ROWS : 0; $blank > 0; $blank--) {
            $rows[] = [PrintedDocument::cell(''), PrintedDocument::cell(''), PrintedDocument::cell('')];
        }

        $document->table(self::ENTRY_WIDTHS, $rows);
    }

    /** @return array<int, array<string, mixed>> */
    private function what(LogbookEntry $entry): array
    {
        $color = match ($entry->operation_milestone) {
            OperationMilestone::Opened => Palette::OPENED_RED,
            OperationMilestone::Closed => Palette::CLOSED_GREEN,
            default => null,
        };
        $lines = [];

        if ($entry->corrects) {
            $lines[] = ['text' => __('logbook::logbook.print.rectifies', ['time' => $entry->corrects->occurred_at->format('d/m/Y H:i')]), 'italic' => true];
        }

        $lines[] = ['text' => $entry->content, 'color' => $color, 'strike' => $entry->isCancelled()];

        if ($entry->isCancelled()) {
            $lines[] = ['text' => __('ui.deletion.cancelled', ['name' => $entry->canceller?->fullName() ?? '-', 'date' => $entry->cancelled_at->format('d/m/Y H:i'), 'reason' => $entry->cancellation_reason]), 'italic' => true, 'color' => Palette::OPENED_RED];
        }

        if ($entry->vessels->isNotEmpty() && $entry->operation_milestone === null) {
            $lines[] = ['text' => $entry->vessels->pluck('name')->join(', ')];
        }

        if ($entry->observation) {
            $lines[] = ['text' => __('logbook::logbook.entries.observation', ['id' => $entry->observation->id]), 'italic' => true];
        } elseif ($entry->surveillanceObservation) {
            $lines[] = ['text' => __('logbook::logbook.entries.surveillance_observation', ['id' => $entry->surveillanceObservation->id]), 'italic' => true];
        }

        if ($entry->witnessReport) {
            $lines[] = ['text' => __('logbook::logbook.entries.witness_report', ['id' => $entry->witnessReport->id]), 'italic' => true];
        }

        if ($entry->operation) {
            $lines[] = ['text' => __('logbook::logbook.entries.operation_'.($entry->operation_milestone?->value ?? 'linked'), ['code' => $entry->operation->code]), 'color' => $color, 'bold' => $color !== null];
        }

        if ($entry->is_order_or_decision) {
            $lines[] = ['text' => __('logbook::logbook.entries.order_or_decision'), 'bold' => true];
        }

        return $lines;
    }

    /** @param array<string, mixed>|null $counters */
    private function addClosing(PrintedDocument $document, ?array $counters, ?string $instructions, ?User $closer, ?Carbon $closedAt): void
    {
        $orange = Palette::CLOSING_ORANGE;
        $label = fn (string $text, int $span = 1) => PrintedDocument::cell($text, ['span' => $span, 'fill' => $orange]);
        $value = fn (?string $text, int $span = 1) => PrintedDocument::cell($text, ['span' => $span]);
        $filled = $counters !== null;
        $opened = $counters['operations_opened'] ?? [];
        $closed = $counters['operations_closed'] ?? [];

        $rows = [
            [PrintedDocument::banner(__('logbook::logbook.days.closing_block'), $orange, 4, '000000')],
            [$label(__('logbook::logbook.handovers.fields.date')), $value($closedAt?->format('d/m/Y')), $label(__('logbook::logbook.handovers.fields.time')), $value($closedAt?->format('H:i'))],
            [$label(__('logbook::logbook.handovers.fields.outgoing'), 2), $value($closer?->fullName(), 2)],
            ...$this->counterRows($counters['operation_counters'] ?? [], $orange, $orange, $filled),
            ...$this->openOperationRows($counters['open_operations'] ?? [], $orange, $filled),
            [$label(__('logbook::logbook.handovers.fields.instructions'), 4)],
            ...$this->textRows($instructions),
            [$label(__('logbook::logbook.days.counters.operations_opened'), 2), $label(__('logbook::logbook.days.counters.operations_closed'), 2)],
        ];

        foreach (OperationCategoryCode::cases() as $code) {
            $rows[] = [
                $label(strtoupper($code->value)), $value($filled ? (string) ($opened[$code->value] ?? 0) : null),
                $label(strtoupper($code->value)), $value($filled ? (string) ($closed[$code->value] ?? 0) : null),
            ];
        }

        $document->table(self::CARTOUCHE_WIDTHS, $rows);
    }

    /**
     * @param  array<string, int>  $counters
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function counterRows(array $counters, string $fill, string $altFill, bool $filled): array
    {
        $number = fn (string $code) => $filled ? str_pad((string) ($counters[$code] ?? 0), 3, '0', STR_PAD_LEFT) : null;
        $label = fn (string $text, string $color) => PrintedDocument::cell($text, ['fill' => $color]);

        return [
            [PrintedDocument::cell(__('logbook::logbook.days.counters.operation_counters'), ['span' => 4, 'fill' => $fill])],
            [$label('VMS', $fill), PrintedDocument::cell($number('vms')), $label('ERS', $altFill), PrintedDocument::cell($number('ers'))],
            [$label('MEP', $fill), PrintedDocument::cell($number('mep')), $label('INN', $altFill), PrintedDocument::cell($number('inn'))],
            [$label('DIV', $fill), PrintedDocument::cell($number('div')), $label('', $altFill), PrintedDocument::cell('')],
        ];
    }

    /**
     * @param  array<string, array<int, string>>  $open
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function openOperationRows(array $open, ?string $labelFill, bool $filled): array
    {
        $rows = [[PrintedDocument::cell(__('logbook::logbook.days.counters.open_operations'), ['span' => 4, 'fill' => $labelFill ?? Palette::LABEL_BLUE])]];

        foreach (OperationCategoryCode::cases() as $code) {
            $codes = $open[$code->value] ?? [];
            $text = ! $filled ? null : ($codes === [] ? __('logbook::logbook.days.counters.none') : collect($codes)->map(fn (string $operation) => explode('-', $operation)[1] ?? $operation)->join(' / '));
            $rows[] = [PrintedDocument::cell(strtoupper($code->value), ['fill' => $labelFill]), PrintedDocument::cell($text, ['span' => 3])];
        }

        return $rows;
    }

    /** @return array<int, array<int, array<string, mixed>>> */
    private function textRows(?string $text): array
    {
        $lines = collect(preg_split('/\R/', (string) $text))->map(fn (string $line) => trim($line))->filter()->values();

        if ($lines->isEmpty()) {
            return array_fill(0, self::BLANK_ROWS, [PrintedDocument::cell('', ['span' => 4])]);
        }

        return $lines->map(fn (string $line) => [PrintedDocument::cell($line, ['span' => 4])])->all();
    }
}
