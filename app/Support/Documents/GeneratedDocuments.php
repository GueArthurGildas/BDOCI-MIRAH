<?php

namespace App\Support\Documents;

use App\Models\LogbookDay;
use App\Models\Observation;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Logbook\LogbookDayWord;
use App\Support\Logbook\ObservationPdf;
use App\Support\Vessels\VesselSheet;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class GeneratedDocuments
{
    public const VESSEL_SHEET = 'vessel_sheet';

    public const LOGBOOK_DAY = 'logbook_day';

    public const OBSERVATION = 'observation';

    public const KINDS = [self::VESSEL_SHEET, self::LOGBOOK_DAY, self::OBSERVATION];

    public function __construct(
        private readonly VesselSheet $vesselSheet,
        private readonly LogbookDayWord $logbookDay,
        private readonly ObservationPdf $observation,
    ) {}

    /** @return class-string<Model> */
    public static function subjectClass(string $kind): string
    {
        return match ($kind) {
            self::VESSEL_SHEET => Vessel::class,
            self::LOGBOOK_DAY => LogbookDay::class,
            self::OBSERVATION => Observation::class,
            default => throw new InvalidArgumentException("Unknown generated document kind [{$kind}]"),
        };
    }

    public function make(string $kind, Model $subject, ?User $generatedBy = null): GeneratedDocument
    {
        return match (true) {
            $kind === self::VESSEL_SHEET && $subject instanceof Vessel => $this->vesselSheet->document($subject, $generatedBy),
            $kind === self::LOGBOOK_DAY && $subject instanceof LogbookDay => new GeneratedDocument($this->logbookDay->filename($subject), $this->logbookDay->render($subject), LogbookDayWord::MIME_TYPE),
            $kind === self::OBSERVATION && $subject instanceof Observation => $this->observation->document($subject),
            default => throw new InvalidArgumentException("Document [{$kind}] cannot be generated for ".$subject::class),
        };
    }
}
