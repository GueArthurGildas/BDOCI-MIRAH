<?php

namespace App\Support\Logbook;

use App\Models\LogbookDay;
use App\Models\LogbookEntry;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Style\Table;

final class LogbookDayWord
{
    public const MIME_TYPE = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private const HEADER_FILL = 'DDDDDD';

    private const BORDER_COLOR = '999999';

    public function __construct(private readonly LogbookCounters $counters) {}

    public function filename(LogbookDay $day): string
    {
        return 'Main_courante_'.$day->day->format('Y-m-d').'.docx';
    }

    public function render(LogbookDay $day): string
    {
        Settings::setOutputEscapingEnabled(true);

        $day->loadMissing(['entries.channel', 'entries.vessels', 'entries.operation', 'entries.author', 'closing.closer']);
        $counters = $this->counters->for($day);

        $word = new PhpWord;
        $word->setDefaultFontName('Calibri');
        $word->setDefaultFontSize(10);
        $section = $word->addSection(['orientation' => 'landscape', 'marginLeft' => 700, 'marginRight' => 700, 'marginTop' => 700, 'marginBottom' => 700]);

        $section->addText(__('logbook::logbook.pdf.title', ['day' => $day->day->format('d/m/Y')]), ['bold' => true, 'size' => 16]);
        $section->addText(__('logbook::logbook.days.shift_range', ['from' => $day->shift_start_at->format('H:i'), 'to' => $day->shift_end_at->format('H:i')]));
        $section->addText(__('logbook::logbook.days.fields.status').' : '.__($day->status->label()));
        $section->addTextBreak();

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => self::BORDER_COLOR,
            'cellMargin' => 60,
            'layout' => Table::LAYOUT_FIXED,
            'width' => 5000,
            'unit' => 'pct',
        ]);

        $table->addRow(null, ['tblHeader' => true]);

        foreach ([__('logbook::logbook.pdf.when') => 1400, __('logbook::logbook.pdf.how') => 2600, __('logbook::logbook.pdf.what') => 7600, __('logbook::logbook.entries.fields.handling_status') => 2000] as $title => $width) {
            $table->addCell($width, ['bgColor' => self::HEADER_FILL])->addText($title, ['bold' => true]);
        }

        foreach ($day->entries as $entry) {
            $this->addEntryRow($table, $entry);
        }

        $section->addTextBreak();
        $section->addText(implode(' · ', [
            __('logbook::logbook.days.counters.entries_total').' : '.$counters['entries_total'],
            __('logbook::logbook.days.counters.received').' : '.$counters['received'],
            __('logbook::logbook.days.counters.sent').' : '.$counters['sent'],
            __('logbook::logbook.days.counters.orders_or_decisions').' : '.$counters['orders_or_decisions'],
            __('logbook::logbook.days.counters.corrections').' : '.$counters['corrections'],
        ]));

        $this->addParticipants($section, $day);

        if ($day->closing) {
            $section->addTextBreak();
            $section->addText(__('logbook::logbook.days.fields.closed_by').' : '.($day->closing->closer?->name ?? '—'), ['italic' => true]);
        }

        $path = tempnam(sys_get_temp_dir(), 'docx');
        IOFactory::createWriter($word, 'Word2007')->save($path);
        $content = (string) file_get_contents($path);
        unlink($path);

        return $content;
    }

    private function addEntryRow(mixed $table, LogbookEntry $entry): void
    {
        $table->addRow();
        $table->addCell(1400)->addText($entry->occurred_at->format('H:i').' UTC');
        $table->addCell(2600)->addText($entry->channel->label.' ('.__($entry->direction->label()).')');

        $what = $table->addCell(7600);
        $what->addText($entry->content);

        if ($entry->is_order_or_decision) {
            $what->addText(__('logbook::logbook.entries.order_or_decision'), ['bold' => true]);
        }

        if ($entry->vessels->isNotEmpty()) {
            $what->addText($entry->vessels->pluck('name')->implode(', '));
        }

        if ($entry->operation) {
            $what->addText($entry->operation->code);
        }

        if ($entry->corrects_entry_id) {
            $what->addText(__('logbook::logbook.pdf.rectifying_line'), ['italic' => true]);
        }

        $table->addCell(2000)->addText(__($entry->handling_status->label()));
    }

    private function addParticipants(mixed $section, LogbookDay $day): void
    {
        $vessels = $day->entries->flatMap->vessels->unique('id')->pluck('name')->sort()->values();
        $actors = $day->entries->pluck('author')->filter()->map(fn ($author) => $author->fullName())->unique()->sort()->values();

        if ($vessels->isNotEmpty()) {
            $section->addTextBreak();
            $section->addText(__('logbook::logbook.folders.vessels').' : '.$vessels->implode(', '));
        }

        if ($actors->isNotEmpty()) {
            $section->addText(__('logbook::logbook.folders.actors').' : '.$actors->implode(', '));
        }
    }
}
