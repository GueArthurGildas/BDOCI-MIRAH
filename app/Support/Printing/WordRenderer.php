<?php

namespace App\Support\Printing;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Style\Table as TableStyle;

final class WordRenderer
{
    public const MIME_TYPE = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private const PAGE_WIDTH = 11900;

    private const PAGE_HEIGHT = 16840;

    private const MARGIN = 1417;

    private const FONT = 'Calibri';

    private const FONT_SIZE = 10;

    private const TIGHT = ['spaceAfter' => 0, 'spaceBefore' => 0];

    public function render(PrintedDocument $document): string
    {
        Settings::setOutputEscapingEnabled(true);

        $word = new PhpWord;
        $word->setDefaultFontName(self::FONT);
        $word->setDefaultFontSize(self::FONT_SIZE);

        $section = $word->addSection([
            'pageSizeW' => self::PAGE_WIDTH,
            'pageSizeH' => self::PAGE_HEIGHT,
            'marginTop' => self::MARGIN,
            'marginBottom' => self::MARGIN,
            'marginLeft' => self::MARGIN,
            'marginRight' => self::MARGIN,
            'headerHeight' => 708,
            'footerHeight' => 708,
        ]);

        $this->addHeaders($section, $document);

        foreach ($document->blocks() as $block) {
            match ($block['type']) {
                'table' => $this->addTable($section, $block['widths'], $block['rows']),
                'paragraph' => $section->addText($block['text'], ['bold' => $block['bold']], self::TIGHT),
                'bullets' => array_map(fn (string $item) => $section->addListItem($item, 0, null, null, self::TIGHT), $block['items']),
                'spacer' => $section->addTextBreak(1, null, self::TIGHT),
            };
        }

        $path = tempnam(sys_get_temp_dir(), 'docx');
        IOFactory::createWriter($word, 'Word2007')->save($path);
        $content = (string) file_get_contents($path);
        unlink($path);

        return $content;
    }

    private function addHeaders(Section $section, PrintedDocument $document): void
    {
        $first = $section->addHeader('first');

        foreach ($document->firstPageHeader as $index => $line) {
            $first->addText($line, ['bold' => $index < 2, 'size' => $index < 2 ? 12 : 11], ['alignment' => Jc::CENTER, ...self::TIGHT]);
        }

        $first->addText($document->filename('docx'), ['size' => 9, 'italic' => true], ['alignment' => Jc::CENTER, ...self::TIGHT]);

        $section->addHeader()->addText($document->runningHeader, ['size' => 9], ['alignment' => Jc::CENTER, ...self::TIGHT]);

        foreach (['first', 'default'] as $type) {
            $footer = $section->addFooter($type);
            $footer->addText($document->filename('docx'), ['size' => 8], ['alignment' => Jc::CENTER, ...self::TIGHT]);
            $footer->addPreserveText('{PAGE} / {NUMPAGES}', ['size' => 8], ['alignment' => Jc::END, ...self::TIGHT]);
        }
    }

    /**
     * @param  array<int, int>  $widths
     * @param  array<int, array<int, array<string, mixed>>>  $rows
     */
    private function addTable(Section $section, array $widths, array $rows): void
    {
        $usable = self::PAGE_WIDTH - 2 * self::MARGIN;
        $twips = array_map(fn (int $percent) => (int) round($usable * $percent / 100), $widths);

        /** @var Table $table */
        $table = $section->addTable([
            'borderSize' => 4,
            'borderColor' => TemplatePalette::BORDER,
            'cellMarginLeft' => 70,
            'cellMarginRight' => 70,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => $usable,
        ]);

        foreach ($rows as $row) {
            $table->addRow();
            $column = 0;

            foreach ($row as $cell) {
                $span = $cell['span'];
                $width = array_sum(array_slice($twips, $column, $span));
                $column += $span;

                $wordCell = $table->addCell($width, array_filter([
                    'gridSpan' => $span > 1 ? $span : null,
                    'bgColor' => $cell['fill'],
                    'valign' => 'top',
                ]));

                foreach ($cell['lines'] as $line) {
                    foreach (explode("\n", $line['text']) as $text) {
                        $wordCell->addText($text, array_filter([
                            'bold' => $line['bold'] ?: null,
                            'italic' => $line['italic'] ?: null,
                            'strikethrough' => $line['strike'] ?: null,
                            'color' => $line['color'],
                        ]), self::TIGHT);
                    }
                }
            }
        }

        $section->addTextBreak(1, null, self::TIGHT);
    }
}
