<?php

namespace Modules\Witnesses\Actions;

use App\Enums\LogbookDayStatus;
use App\Enums\ReportChannel;
use App\Enums\WitnessCategory;
use App\Enums\WitnessReportStatus;
use App\Models\Vessel;
use App\Models\Witness;
use App\Models\WitnessReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\LogbookCalendar;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\LogbookWriter;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RecordWitnessReport extends Action
{
    private const MAX_VESSELS = 2;

    private const LOGBOOK_CHANNELS = [
        'email' => 'email',
        'phone' => 'phone',
        'mail' => 'mail',
        'form' => 'mail',
    ];

    private const VESSEL_FIELDS = ['latitude', 'longitude', 'name', 'flag', 'registration_port', 'type', 'ircs', 'registration', 'other_marks', 'gear', 'is_fishing', 'route', 'is_drifting', 'is_resupplying', 'is_transshipping', 'description'];

    public function __construct(
        private readonly LogbookWriter $logbook,
        private readonly LogbookChannels $channels,
        private readonly LogbookCalendar $calendar,
    ) {}

    public function code(): string
    {
        return 'witnesses.record';
    }

    public function permission(): Permission
    {
        return Permission::WitnessesCreate;
    }

    public function handle(ActionContext $context, array $input): WitnessReport
    {
        $receivedAt = filled($input['received_at'] ?? null) ? Carbon::parse($input['received_at']) : now();

        if ($this->calendar->dayFor($receivedAt)->status !== LogbookDayStatus::Open) {
            throw ValidationException::withMessages(['received_at' => __('witnesses::witnesses.errors.logbook_day_closed')]);
        }

        $report = WitnessReport::create([
            ...Arr::only($input, ['latitude', 'longitude', 'channel']),
            'received_at' => $receivedAt,
            'witness_id' => $this->witness($input)?->id,
            'entered_by' => $context->actor->id,
            'status' => WitnessReportStatus::Received,
        ]);

        foreach (array_slice(array_values($input['vessels'] ?? []), 0, self::MAX_VESSELS) as $index => $vessel) {
            if (! $this->hasContent($vessel)) {
                continue;
            }

            $known = $this->knownVessel($vessel);

            $report->reportedVessels()->create([
                ...Arr::only($vessel, self::VESSEL_FIELDS),
                'rank' => $index + 1,
                'vessel_id' => $known?->id,
            ]);

            if ($known) {
                $context->publish(
                    vessel: $known,
                    module: 'witnesses',
                    type: 'witness_report.received',
                    subject: $report,
                    summaryKey: 'witnesses::events.received',
                    params: ['id' => $report->id],
                    occurredAt: $report->received_at,
                );
            }
        }

        $report->load('reportedVessels');

        $entry = $this->logbook->write($context, [
            'occurred_at' => $report->received_at,
            'channel_id' => $this->channel($report->channel),
            'content' => $this->summary($report),
            'vessel_ids' => $report->reportedVessels->pluck('vessel_id')->filter()->unique()->values()->all(),
            'opens_watch' => false,
            'publishes_vessel_event' => false,
        ]);

        $report->update(['logbook_entry_id' => $entry->id]);

        return $report;
    }

    private function channel(ReportChannel $channel): int
    {
        $code = self::LOGBOOK_CHANNELS[$channel->value] ?? LogbookChannels::INTERNAL;
        $list = $this->channels->ensure();

        return $list->values()->where('code', $code)->value('id') ?? $this->channels->idFor(LogbookChannels::INTERNAL);
    }

    private function summary(WitnessReport $report): string
    {
        $lines = $report->reportedVessels
            ->map(fn ($vessel) => collect([
                collect([$vessel->name, $vessel->ircs, $vessel->registration])->filter()->implode(' / '),
                $vessel->description,
            ])->filter()->implode(' : '))
            ->filter();

        return $lines->isEmpty() ? '—' : $lines->implode(PHP_EOL);
    }

    /** @param array<string, mixed> $input */
    private function witness(array $input): ?Witness
    {
        if (filled($input['witness_id'] ?? null)) {
            return Witness::findOrFail($input['witness_id']);
        }

        if (blank($input['witness_name'] ?? null)) {
            return null;
        }

        return Witness::create([
            'name' => $input['witness_name'],
            'category' => WitnessCategory::from($input['witness_category'] ?? WitnessCategory::Other->value),
            'email' => $input['witness_email'] ?? null,
            'phone' => $input['witness_phone'] ?? null,
        ]);
    }

    /** @param array<string, mixed> $vessel */
    private function hasContent(array $vessel): bool
    {
        return collect(Arr::only($vessel, ['name', 'ircs', 'registration', 'other_marks', 'description']))->filter()->isNotEmpty();
    }

    /** @param array<string, mixed> $vessel */
    private function knownVessel(array $vessel): ?Vessel
    {
        return Vessel::query()
            ->when(filled($vessel['registration'] ?? null), fn ($query) => $query->orWhere('registration_number', $vessel['registration']))
            ->when(filled($vessel['ircs'] ?? null), fn ($query) => $query->orWhere('call_sign', $vessel['ircs']))
            ->when(blank($vessel['registration'] ?? null) && blank($vessel['ircs'] ?? null), fn ($query) => $query->whereRaw('1 = 0'))
            ->first();
    }
}
