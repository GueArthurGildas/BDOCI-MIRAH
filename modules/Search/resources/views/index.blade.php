<x-layouts.app :title="__('search::search.title')">
    <x-ui.page-header :title="__('search::search.title')" :subtitle="__('search::search.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="$canSearch ? __('search::search.results_for', ['term' => $term]) : __('search::search.title')">
            <form method="GET" action="{{ route('search') }}" class="input-group">
                <input type="search" name="q" value="{{ $term }}" class="form-control" placeholder="{{ __('search::search.placeholder') }}" autofocus>
                <button type="submit" class="btn btn-primary"><i class="feather icon-search"></i> {{ __('ui.search') }}</button>
            </form>

            @if (! $canSearch)
                <p class="text-muted m-t-20 m-b-0">{{ __('search::search.too_short', ['count' => Modules\Search\Http\Controllers\SearchController::MINIMUM_TERM_LENGTH]) }}</p>
            @elseif ($resultsBySource->isEmpty())
                <p class="text-muted m-t-20 m-b-0">{{ __('search::search.nothing_found') }}</p>
            @endif
        </x-ui.card>

        <div class="row">
            @foreach ($resultsBySource as $group)
                <div class="col-xl-6">
                    <x-ui.card :title="__('search::search.sources.'.$group['source']->key)" :subtitle="trans_choice('ui.results_count', $group['results']->count())" flush>
                        <ul class="list-group list-group-flush">
                            @foreach ($group['results'] as $result)
                                <li class="list-group-item d-flex align-items-start">
                                    <i class="feather {{ $group['source']->icon }} text-primary m-r-15 m-t-5"></i>
                                    <div>
                                        @if ($result->url)
                                            <a href="{{ $result->url }}" class="f-w-600">{{ $result->title }}</a>
                                        @else
                                            <span class="f-w-600">{{ $result->title }}</span>
                                        @endif
                                        @if ($result->subtitle)
                                            <div class="text-muted f-12">{{ $result->subtitle }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
