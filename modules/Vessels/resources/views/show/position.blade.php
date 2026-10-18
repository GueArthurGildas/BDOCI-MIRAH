@php($position = $vessel->latestPosition)

<x-ui.card :title="__('vessels::vessels.summary.last_position')" :subtitle="$position ? __('vessels::vessels.summary.last_emission').' : '.$position->recorded_at->isoFormat('L LT').' ('.$position->recorded_at->diffForHumans().')' : __('vessels::vessels.summary.no_position')">
    @if ($position)
        <div class="row">
            <div class="col-lg-8">
                <x-ui.map height="260px" :markers="$track->map(fn ($point) => [
                    'latitude' => $point->latitude,
                    'longitude' => $point->longitude,
                    'label' => $point->recorded_at->isoFormat('L LT'),
                    'color' => $point->is($position) ? '#fe5d70' : '#01a9ac',
                ])->all()" />
            </div>
            <div class="col-lg-4">
                <x-ui.detail-list>
                    <x-ui.detail :label="__('vessels::vessels.summary.zone')">{{ $position->zone?->name }}</x-ui.detail>
                    <x-ui.detail :label="__('vessels::vessels.summary.speed')">{{ __('vessels::vessels.summary.speed_value', ['value' => Number::format($position->speed_knots, 1)]) }}</x-ui.detail>
                    <x-ui.detail :label="__('vessels::vessels.summary.coordinates')">{{ Number::format($position->latitude, 4) }}, {{ Number::format($position->longitude, 4) }}</x-ui.detail>
                </x-ui.detail-list>
            </div>
        </div>
    @else
        <p class="text-muted m-0">{{ __('vessels::vessels.summary.no_position') }}</p>
    @endif
</x-ui.card>
