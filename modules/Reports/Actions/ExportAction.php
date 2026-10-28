<?php

namespace Modules\Reports\Actions;

use App\Enums\ExportFormat;
use App\Models\ExportLog;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use Illuminate\Support\Arr;
use Modules\Reports\Support\ExportDatasets;
use Modules\Reports\Support\ExportedFile;
use Modules\Reports\Support\ExportRenderer;
use Modules\Reports\Support\ExportType;

abstract class ExportAction extends Action
{
    public function __construct(private readonly ExportDatasets $datasets, private readonly ExportRenderer $renderer) {}

    abstract protected function type(): ExportType;

    public function code(): string
    {
        return "reports.export_{$this->type()->value}";
    }

    public function handle(ActionContext $context, array $input): ExportedFile
    {
        $format = ExportFormat::from($input['format']);
        $filters = array_filter(Arr::only($input, $this->type()->filterKeys()), fn ($value) => $value !== null && $value !== '');
        $dataset = $this->datasets->build($this->type(), $filters);
        $rendered = $this->renderer->render($dataset, $format);

        $log = ExportLog::create([
            'user_id' => $context->actor->id,
            'subject' => $this->type()->value,
            'permission' => $this->permission()->value,
            'format' => $format,
            'filters' => $filters,
            'row_count' => $dataset->rowCount(),
            'exported_at' => now(),
        ]);

        return new ExportedFile($log, $rendered->content, $rendered->mimeType, "{$this->type()->value}_".now()->format('Ymd_His').".{$rendered->extension}");
    }
}
