<?php

namespace Modules\Logbook\Actions;

use App\Enums\LogbookDayStatus;
use App\Models\LogbookDay;
use App\Models\Operation;
use App\Models\User;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookCounters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

class CloseLogbookDay extends Action
{
    public const PDF_LOCALE = 'fr';

    public function __construct(private readonly DocumentStore $documents, private readonly LogbookCounters $counters) {}

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
        $day->load(['entries.channel', 'entries.vessels', 'entries.operation', 'entries.author', 'handovers.outgoing', 'handovers.incoming', 'handovers.supervisor']);

        $document = $this->documents->storeContent(
            $this->fileName($day),
            $this->renderPdf($day, $counters, $instructions, $context->actor),
            'application/pdf',
            $context->actor,
            freeze: true,
        );
        $this->documents->attach($document, $day);

        $day->closing()->create(['counters' => $counters, 'instructions' => $instructions, 'closed_by' => $context->actor->id, 'closed_at' => now()]);
        $day->update(['status' => LogbookDayStatus::Locked, 'pdf_document_id' => $document->id, 'locked_at' => now()]);

        return $day;
    }

    public function fileName(LogbookDay $day): string
    {
        return 'Main_courante_CSP_du_'.$day->day->format('Y_m_d').'.pdf';
    }

    /** @param array<string, mixed> $counters */
    private function renderPdf(LogbookDay $day, array $counters, ?string $instructions, User $closer): string
    {
        $previous = App::getLocale();
        App::setLocale(self::PDF_LOCALE);

        try {
            return Pdf::loadView('logbook::pdf.day', ['day' => $day, 'counters' => $counters, 'closingInstructions' => $instructions, 'closer' => $closer, 'closedAt' => now(), 'operations' =>Operation::query()->whereIn('id', $day->entries->pluck('operation_id')->filter())->get()->keyBy('id')])
                ->setPaper('a4', 'landscape')
                ->output();
        } finally {
            App::setLocale($previous);
        }
    }
}
