<x-ui.card :title="__('vessels::vessels.tabs.timeline')">
    <form method="GET" action="{{ route('vessels.show', $vessel) }}" class="row g-3 align-items-end m-b-20">
        <input type="hidden" name="tab" value="timeline">
        <x-ui.filter-select name="module" :label="__('vessels::vessels.timeline.module')" :options="$timelineModules->mapWithKeys(fn ($code) => [$code => __('vessels::vessels.modules.'.$code)])->all()" :value="$timelineModule" class="col-md-4" />
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-filter"></i> {{ __('ui.filter') }}</button>
        </div>
    </form>

    @if ($timeline->isEmpty())
        <p class="text-muted m-0">{{ __('vessels::vessels.timeline.empty') }}</p>
    @else
        <div class="main-timeline">
            <div class="cd-timeline cd-container">
                @foreach ($timeline as $event)
                    <div class="cd-timeline-block">
                        <div class="cd-timeline-icon bg-{{ $event['color'] }}">
                            <i class="feather {{ $event['icon'] }}"></i>
                        </div>
                        <div class="cd-timeline-content card_main">
                            <div class="p-20">
                                <h6>{{ $event['title'] }}</h6>
                                <div class="timeline-details">
                                    <p class="m-0 text-muted">
                                        <i class="feather icon-calendar"></i> {{ $event['date']->isoFormat('LL LT') }}
                                        · {{ __('vessels::vessels.modules.'.$event['module']) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-ui.card>
