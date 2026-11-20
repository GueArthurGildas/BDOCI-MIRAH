<?php

namespace App\Support\Printing;

final class PrintedDocument
{
    /** @var array<int, array<string, mixed>> */
    private array $blocks = [];

    /** @param array<int, string> $firstPageHeader */
    public function __construct(
        public readonly string $basename,
        public readonly array $firstPageHeader,
        public readonly string $runningHeader,
    ) {}

    /**
     * @param  array<int, int>  $widths  column widths in percent of the page width
     * @param  array<int, array<int, array<string, mixed>>>  $rows
     */
    public function table(array $widths, array $rows): self
    {
        $this->blocks[] = ['type' => 'table', 'widths' => $widths, 'rows' => $rows];

        return $this;
    }

    public function paragraph(string $text, bool $bold = false): self
    {
        $this->blocks[] = ['type' => 'paragraph', 'text' => $text, 'bold' => $bold];

        return $this;
    }

    /** @param array<int, string> $items */
    public function bullets(array $items): self
    {
        $this->blocks[] = ['type' => 'bullets', 'items' => $items];

        return $this;
    }

    public function spacer(): self
    {
        $this->blocks[] = ['type' => 'spacer'];

        return $this;
    }

    /** @return array<int, array<string, mixed>> */
    public function blocks(): array
    {
        return $this->blocks;
    }

    public function filename(string $extension): string
    {
        return $this->basename.'.'.$extension;
    }

    /**
     * @param  array<string, mixed>  $options  span, fill, bold, color, strike, italic
     * @return array<string, mixed>
     */
    public static function cell(string|array|null $text, array $options = []): array
    {
        $lines = is_array($text) ? $text : [['text' => (string) $text]];

        return [
            'lines' => array_map(fn (array $line) => [
                'text' => (string) ($line['text'] ?? ''),
                'color' => $line['color'] ?? $options['color'] ?? null,
                'bold' => $line['bold'] ?? $options['bold'] ?? false,
                'italic' => $line['italic'] ?? $options['italic'] ?? false,
                'strike' => $line['strike'] ?? $options['strike'] ?? false,
            ], $lines),
            'span' => $options['span'] ?? 1,
            'fill' => $options['fill'] ?? null,
        ];
    }

    /** @return array<string, mixed> */
    public static function banner(string $text, string $fill, int $span, string $color = 'FFFFFF'): array
    {
        return self::cell($text, ['span' => $span, 'fill' => $fill, 'bold' => true, 'color' => $color]);
    }
}
