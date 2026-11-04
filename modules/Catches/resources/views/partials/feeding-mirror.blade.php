<x-ui.card :title="__('catches::catches.feeding.title')">
    <ul class="mb-0">
        <li>{{ __('catches::catches.feeding.mirror_source') }}</li>
        <li>
            @if ($dataSource?->last_synced_at)
                {{ __('catches::catches.feeding.mirror_last_sync', ['date' => $dataSource->last_synced_at->isoFormat('L LT'), 'relative' => $dataSource->last_synced_at->diffForHumans()]) }}
            @else
                {{ __('catches::catches.feeding.mirror_never') }}
            @endif
        </li>
        <li>{{ __('catches::catches.feeding.mirror_origins') }}
            @foreach ($originCounts as $origin => $total)
                <span class="label label-info">{{ __('enums.mirror_origin.'.$origin) }} : {{ $total }}</span>
            @endforeach
        </li>
        <li>{{ __('catches::catches.feeding.mirror_fallback') }}</li>
    </ul>
</x-ui.card>
