<?php

namespace Modules\Vessels\Actions;

use App\Enums\MatchingStatus;
use App\Enums\RecordStatus;
use App\Enums\SourceSystem;
use App\Models\VesselSource;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Modules\Vessels\Support\MatchResult;
use Modules\Vessels\Support\VesselMatcher;
use Modules\Vessels\Support\VesselRecordWriter;

class IngestVesselSource extends Action
{
    public function __construct(private readonly VesselMatcher $matcher, private readonly VesselRecordWriter $writer) {}

    public function code(): string
    {
        return 'vessels.ingest_source';
    }

    public function permission(): Permission
    {
        return Permission::VesselsManage;
    }

    public function handle(ActionContext $context, array $input): VesselSource
    {
        $source = SourceSystem::from($input['source']);
        $link = filled($input['external_id'] ?? null)
            ? VesselSource::where('source', $source->value)->where('external_id', $input['external_id'])->first()
            : null;

        if ($link?->vessel_id !== null && $link->matching_status !== MatchingStatus::PossibleDuplicate) {
            return $this->refresh($context, $link, $input, $source);
        }

        if ($link !== null) {
            $link->update(['raw_data' => $input, 'last_import_at' => now()]);

            return $link;
        }

        $result = $this->matcher->match($input);

        return match ($result->status) {
            MatchingStatus::Matched => $this->link($context, $input, $source, $result),
            MatchingStatus::PossibleDuplicate => $this->flag($context, $input, $source, $result),
            default => $this->createNew($context, $input, $source),
        };
    }

    private function refresh(ActionContext $context, VesselSource $link, array $input, SourceSystem $source): VesselSource
    {
        $vessel = $link->vessel;
        $this->writer->enrich($vessel, $input, $source);
        $link->update(['raw_data' => $input, 'last_import_at' => now()]);

        $context->publish($vessel, 'vessels', 'vessel.source_refreshed', $link, 'vessels::events.source_refreshed', ['name' => $vessel->name, 'source' => $source->value]);

        return $link;
    }

    private function link(ActionContext $context, array $input, SourceSystem $source, MatchResult $result): VesselSource
    {
        $vessel = $result->candidate;
        $this->writer->enrich($vessel, $input, $source);

        $link = $this->newLink($input, $source, [
            'vessel_id' => $vessel->id,
            'matching_status' => MatchingStatus::Matched,
            'matching_criterion' => $result->criterion,
        ]);

        $context->publish($vessel, 'vessels', 'vessel.source_linked', $link, 'vessels::events.source_linked', [
            'name' => $vessel->name, 'source' => $source->value, 'criterion' => $result->criterion->value,
        ]);

        return $link;
    }

    private function flag(ActionContext $context, array $input, SourceSystem $source, MatchResult $result): VesselSource
    {
        $vessel = $this->writer->create($input, $source, RecordStatus::ToMatch);

        $link = $this->newLink($input, $source, [
            'vessel_id' => $vessel->id,
            'matching_status' => MatchingStatus::PossibleDuplicate,
            'candidate_vessel_id' => $result->candidate->id,
            'matching_criterion' => $result->criterion,
        ]);

        $context->publish($result->candidate, 'vessels', 'vessel.duplicate_flagged', $link, 'vessels::events.duplicate_flagged', [
            'name' => $vessel->name, 'source' => $source->value,
        ]);

        return $link;
    }

    private function createNew(ActionContext $context, array $input, SourceSystem $source): VesselSource
    {
        $vessel = $this->writer->create($input, $source, RecordStatus::Validated);
        $link = $this->newLink($input, $source, ['vessel_id' => $vessel->id, 'matching_status' => MatchingStatus::New]);

        $context->publish($vessel, 'vessels', 'vessel.created_from_source', $link, 'vessels::events.created_from_source', [
            'name' => $vessel->name, 'source' => $source->value,
        ]);

        return $link;
    }

    private function newLink(array $input, SourceSystem $source, array $attributes): VesselSource
    {
        return VesselSource::create([
            'source' => $source,
            'external_id' => $input['external_id'] ?? null,
            'last_import_at' => now(),
            'raw_data' => $input,
            ...$attributes,
        ]);
    }
}
