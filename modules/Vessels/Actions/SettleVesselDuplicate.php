<?php

namespace Modules\Vessels\Actions;

use App\Enums\DuplicateDecision;
use App\Enums\MatchingStatus;
use App\Enums\RecordStatus;
use App\Models\Vessel;
use App\Models\VesselActor;
use App\Models\VesselFieldProvenance;
use App\Models\VesselHistory;
use App\Models\VesselIdentifier;
use App\Models\VesselSource;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\DB;
use Modules\Vessels\Support\VesselRecordWriter;

class SettleVesselDuplicate extends Action
{
    public function __construct(private readonly VesselRecordWriter $writer) {}

    public function code(): string
    {
        return 'vessels.settle_duplicate';
    }

    public function permission(): Permission
    {
        return Permission::VesselsMatch;
    }

    public function handle(ActionContext $context, array $input): VesselSource
    {
        $link = VesselSource::where('matching_status', MatchingStatus::PossibleDuplicate->value)->findOrFail($input['vessel_source_id']);
        $decision = DuplicateDecision::from($input['decision']);

        match ($decision) {
            DuplicateDecision::Merge => $this->merge($context, $link),
            DuplicateDecision::KeepSeparate => $this->keepSeparate($context, $link),
            DuplicateDecision::Reject => $this->reject($context, $link),
        };

        return $link->refresh();
    }

    private function merge(ActionContext $context, VesselSource $link): void
    {
        $duplicate = Vessel::findOrFail($link->vessel_id);
        $target = Vessel::findOrFail($link->candidate_vessel_id);
        $columns = $duplicate->only(['registration_number', 'imo_number', 'call_sign']);

        VesselIdentifier::where('vessel_id', $duplicate->id)->update(['vessel_id' => $target->id]);
        VesselActor::where('vessel_id', $duplicate->id)->update(['vessel_id' => $target->id]);
        VesselHistory::where('vessel_id', $duplicate->id)->update(['vessel_id' => $target->id]);
        VesselSource::where('vessel_id', $duplicate->id)->update(['vessel_id' => $target->id]);
        VesselFieldProvenance::where('vessel_id', $duplicate->id)->delete();
        DB::table('external_list_vessel')->where('vessel_id', $duplicate->id)->whereNotIn('external_list_id', DB::table('external_list_vessel')->where('vessel_id', $target->id)->select('external_list_id'))->update(['vessel_id' => $target->id]);
        DB::table('external_list_vessel')->where('vessel_id', $duplicate->id)->delete();

        $this->retire($duplicate);

        $target->refresh();
        $target->update(collect($columns)->filter(fn ($value, $column) => $value !== null && $target->{$column} === null)->all());
        $this->writer->enrich($target, $link->raw_data ?? [], $link->source);

        $this->settle($context, $link, MatchingStatus::Matched, $target->id);

        $context->publish($target, 'vessels', 'vessel.duplicate_merged', $link, 'vessels::events.duplicate_merged', [
            'name' => $target->name, 'source' => $link->source->value,
        ]);
    }

    private function keepSeparate(ActionContext $context, VesselSource $link): void
    {
        $vessel = Vessel::findOrFail($link->vessel_id);
        $vessel->update(['record_status' => RecordStatus::Validated]);

        $this->settle($context, $link, MatchingStatus::New, $vessel->id);

        $context->publish($vessel, 'vessels', 'vessel.duplicate_kept', $link, 'vessels::events.duplicate_kept', [
            'name' => $vessel->name, 'source' => $link->source->value,
        ]);
    }

    private function reject(ActionContext $context, VesselSource $link): void
    {
        $duplicate = Vessel::findOrFail($link->vessel_id);
        $candidate = Vessel::findOrFail($link->candidate_vessel_id);

        $this->retire($duplicate);
        $this->settle($context, $link, MatchingStatus::Rejected, null);

        $context->publish($candidate, 'vessels', 'vessel.duplicate_rejected', $link, 'vessels::events.duplicate_rejected', [
            'name' => $duplicate->name, 'source' => $link->source->value,
        ]);
    }

    private function retire(Vessel $duplicate): void
    {
        DB::table('vessels')->where('id', $duplicate->id)->update([
            'registration_number' => null,
            'imo_number' => null,
            'call_sign' => null,
            'fao_identifier' => null,
        ]);

        VesselIdentifier::where('vessel_id', $duplicate->id)->whereNull('valid_to')->update(['valid_to' => today()]);
        $duplicate->delete();
    }

    private function settle(ActionContext $context, VesselSource $link, MatchingStatus $status, ?int $vesselId): void
    {
        $link->update([
            'vessel_id' => $vesselId,
            'matching_status' => $status,
            'settled_by' => $context->actor->id,
            'settled_at' => now(),
        ]);
    }
}
