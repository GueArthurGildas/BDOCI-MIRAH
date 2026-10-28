<?php

namespace Modules\Reports\Support;

use App\Models\ExportLog;

final readonly class ExportedFile
{
    public function __construct(
        public ExportLog $log,
        public string $content,
        public string $mimeType,
        public string $fileName,
    ) {}
}
