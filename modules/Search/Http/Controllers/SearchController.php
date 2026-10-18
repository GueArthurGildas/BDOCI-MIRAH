<?php

namespace Modules\Search\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Search\Support\SearchSource;
use Modules\Search\Support\SearchSources;

class SearchController extends Controller
{
    public const MINIMUM_TERM_LENGTH = 2;

    public function __invoke(Request $request): View
    {
        $term = trim((string) $request->string('q'));
        $canSearch = mb_strlen($term) >= self::MINIMUM_TERM_LENGTH;

        $resultsBySource = $canSearch
            ? SearchSources::availableTo($request->user())
                ->map(fn (SearchSource $source) => ['source' => $source, 'results' => $source->search($term)])
                ->filter(fn (array $group) => $group['results']->isNotEmpty())
            : collect();

        return view('search::index', compact('term', 'canSearch', 'resultsBySource'));
    }
}
