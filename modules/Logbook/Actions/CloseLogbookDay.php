<?php

namespace Modules\Logbook\Actions;

use App\Enums\LogbookDayStatus;
use App\Models\LogbookDay;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookCounters;
use App\Support\Logbook\LogbookDayDocument;
use App\Support\Printing\PdfRenderer;
use Illuminate\Validation\ValidationException;

class CloseLogbookDay extends Action
{
    public const PDF_LOCALE = PdfRenderer::LOCALE;

    public function __construct(
        private readonly DocumentStore $documents,
        private readonly LogbookCounters $counters,
        private readonly LogbookDayDocument $printed,
        private readonly PdfRenderer $pdf,
    ) {}

    public function code(): string
    {
        return 'logbook.close_day';
    }

    public function permission(): Permission
    {
        return Permission::LogbookClose;
    }

    public function handle(ActionContext $context, array $input): LogbookDay
    {
        $day = LogbookDay::lockForUpdate()->findOrFail($input['day_id']);

        if ($day->status !== LogbookDayStatus::Open) {
            throw ValidationException::withMessages(['day' => __('logbook::logbook.errors.day_not_open')]);
        }

        if (! $day->hasEnded()) {
            throw ValidationException::withMessages(['day' => __('logbook::logbook.errors.day_not_ended', ['time' => $day->shift_end_at->format('d/m/Y H:i')])]);
        }

        $counters = $this->counters->for($day);
        $instructions = filled($input['instructions'] ?? null) ? $input['instructions'] : null;
        $closedAt = now();

        $content = PdfRenderer::inPrintLocale(fn () => $this->pdf->render($this->printed->build($day, $counters, $instructions, $context->actor, $closedAt)));
        $document = $this->documents->storeContent($this->fileName($day), $content, 'application/pdf', $context->actor, freeze: true);
        $this->documents->attach($document, $day);

        $day->closing()->create(['counters' => $counters, 'instructions' => $instructions, 'closed_by' => $context->actor->id, 'closed_at' => $closedAt]);
        $day->update(['status' => LogbookDayStatus::Locked, 'pdf_document_id' => $document->id, 'locked_at' => $closedAt]);

        return $day;
    }

    public function fileName(LogbookDay $day): string
    {
        return LogbookDayDocument::basename($day).'.pdf';
    }
}
