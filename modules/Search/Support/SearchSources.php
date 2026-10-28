<?php

namespace Modules\Search\Support;

use App\Models\ExternalInspection;
use App\Models\InfractionCase;
use App\Models\License;
use App\Models\Mission;
use App\Models\Observation;
use App\Models\Vessel;
use App\Support\Authorization\Permission;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

final class SearchSources
{
    private const RESULTS_PER_SOURCE = 10;

    /** @return Collection<int, SearchSource> */
    public static function availableTo(Authorizable $user): Collection
    {
        return self::all()->filter(fn (SearchSource $source) => $user->can($source->permission))->values();
    }

    /** @return Collection<int, SearchSource> */
    private static function all(): Collection
    {
        return collect([
            new SearchSource('vessels', 'icon-anchor', Permission::VesselsView->value, fn (string $term) => Vessel::search($term)
                ->limit(self::RESULTS_PER_SOURCE)->get()
                ->map(fn (Vessel $vessel) => new SearchResult($vessel->name, $vessel->registration_number, self::url('vessels.show', $vessel)))),

            new SearchSource('licenses', 'icon-file-text', Permission::LicensesView->value, fn (string $term) => License::with('vessel')
                ->whereLike('number', "%{$term}%")
                ->limit(self::RESULTS_PER_SOURCE)->get()
                ->map(fn (License $license) => new SearchResult($license->number, $license->vessel->name, self::url('licenses.index', ['search' => $license->number])))),

            new SearchSource('inspections', 'icon-check-square', Permission::InspectionsView->value, fn (string $term) => ExternalInspection::with('vessel')
                ->where(fn (Builder $query) => $query->whereLike('reference', "%{$term}%")->orWhereHas('vessel', fn (Builder $query) => $query->search($term)))
                ->latest('started_at')->limit(self::RESULTS_PER_SOURCE)->get()
                ->map(fn (ExternalInspection $inspection) => new SearchResult($inspection->reference ?? $inspection->external_id, ($inspection->vessel?->name ?? '—').' — '.($inspection->started_at?->isoFormat('L') ?? '—'), self::url('inspections.show', $inspection)))),

            new SearchSource('infractions', 'icon-alert-triangle', Permission::InfractionsView->value, fn (string $term) => InfractionCase::with(['vessel', 'upheld.type'])
                ->where(fn (Builder $query) => $query->whereLike('reference', "%{$term}%")->orWhereHas('vessel', fn (Builder $query) => $query->search($term)))
                ->latest('opened_at')->limit(self::RESULTS_PER_SOURCE)->get()
                ->map(fn (InfractionCase $case) => new SearchResult($case->reference, $case->vessel->name.' — '.($case->upheld->map(fn ($upheld) => $upheld->type->label)->implode(', ') ?: $case->facts), self::url('infractions.show', $case)))),

            new SearchSource('missions', 'icon-flag', Permission::MissionsView->value, fn (string $term) => Mission::query()
                ->where(fn (Builder $query) => $query->whereLike('code', "%{$term}%")->orWhereLike('objectives', "%{$term}%"))
                ->latest('planned_start_at')->limit(self::RESULTS_PER_SOURCE)->get()
                ->map(fn (Mission $mission) => new SearchResult($mission->code, (string) $mission->objectives, self::url('missions.show', $mission)))),

            new SearchSource('observations', 'icon-book', Permission::LogbookView->value, fn (string $term) => Observation::with('vessel')
                ->where(fn (Builder $query) => $query->whereLike('description', "%{$term}%")->orWhereHas('vessel', fn (Builder $query) => $query->search($term)))
                ->latest('observed_at')->limit(self::RESULTS_PER_SOURCE)->get()
                ->map(fn (Observation $observation) => new SearchResult(Str::limit($observation->description, 80), $observation->observed_at->isoFormat('L LT'), self::url('logbook.show', $observation)))),
        ]);
    }

    private static function url(string $route, mixed $parameters): ?string
    {
        return Route::has($route) ? route($route, $parameters) : null;
    }
}
