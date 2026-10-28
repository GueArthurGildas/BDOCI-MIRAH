<?php

namespace Modules\Reports\Support;

final readonly class ExportDataset
{
    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, scalar|null>>  $rows
     */
    public function __construct(public string $title, public array $headings, public array $rows) {}

    public function rowCount(): int
    {
        return count($this->rows);
    }
}
