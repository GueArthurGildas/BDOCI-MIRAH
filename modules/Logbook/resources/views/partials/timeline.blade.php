@php
    $kinds = [
        'received' => ['icon' => 'icon-arrow-down-left', 'color' => 'info'],
        'sent' => ['icon' => 'icon-arrow-up-right', 'color' => 'success'],
        'decision' => ['icon' => 'icon-alert-octagon', 'color' => 'danger'],
        'correction' => ['icon' => 'icon-edit-2', 'color' => 'warning'],
    ];
@endphp

<x-ui.card :title="__('logbook::logbook.timeline.title')" :subtitle="__('logbook::logbook.timeline.subtitle')">
    <ul class="gs-timeline-legend">
        @foreach ($kinds as $kind => $style)
            <li><span class="gs-state-icon is-{{ $style['color'] }}"><i class="feather {{ $style['icon'] }}"></i></span> {{ __('logbook::logbook.timeline.legend.'.$kind) }}</li>
        @endforeach
    </ul>

    @if ($entries->isEmpty())
        <p class="text-muted m-0">{{ __('logbook::logbook.timeline.empty') }}</p>
    @else
        <div class="main-timeline">
            <div class="cd-timeline cd-container">
                @foreach ($entries as $entry)
                    @php
                        $kind = match (true) {
                            $entry->corrects_entry_id !== null => 'correction',
                            (bool) $entry->is_order_or_decision => 'decision',
                            $entry->direction === App\Enums\LogbookDirection::Sent => 'sent',
                            default => 'received',
                        };
                    @endphp
                    <div class="cd-timeline-block">
                        <div class="cd-timeline-icon bg-{{ $kinds[$kind]['color'] }}">
                            <i class="feather {{ $kinds[$kind]['icon'] }}"></i>
                        </div>
                        <div class="cd-timeline-content card_main">
                            <div class="p-20">
                                <h6>{{ $entry->occurred_at->format('H:i') }} UTC · {{ $entry->channel->label }}</h6>
                                <p class="text-pre-line m-b-5">{{ Str::limit($entry->content, 220) }}</p>
                                <div class="timeline-details">
                                    <span class="label label-{{ $kinds[$kind]['color'] }}">{{ __('logbook::logbook.timeline.legend.'.$kind) }}</span>
                                    <x-ui.status-badge :status="$entry->handling_status" />
                                    @foreach ($entry->vessels as $vessel)
                                        <span class="d-block f-12"><x-ui.vessel-link :vessel="$vessel" :thumb="false" /></span>
                                    @endforeach
                                    @if ($entry->author)
                                        <span class="d-block text-muted f-12">{{ $entry->author->fullName() }}</span>
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
