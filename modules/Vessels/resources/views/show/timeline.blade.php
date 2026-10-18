<x-ui.card :title="__('vessels::vessels.tabs.timeline')">
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
                                    <p class="m-0 text-muted"><i class="feather icon-calendar"></i> {{ $event['date']->isoFormat('LL') }}</p>
                                    @if ($event['description'])
                                        <p class="m-t-10 m-b-0">{{ $event['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-ui.card>
