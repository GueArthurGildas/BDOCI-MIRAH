<x-ui.card :title="__('catches::catches.feeding.title')">
    <p>{{ __('catches::catches.feeding.catches_text') }}</p>
    <ul class="mb-0">
        @forelse ($sources as $source)
            <li>{{ __($source->source->label()) }} : {{ trans_choice('catches::catches.feeding.lines', $source->total, ['count' => $source->total]) }} ({{ __('catches::catches.feeding.last_update', ['date' => \Illuminate\Support\Carbon::parse($source->last_update)->isoFormat('L LT')]) }})</li>
        @empty
            <li>{{ __('catches::catches.feeding.none') }}</li>
        @endforelse
    </ul>
    <p class="text-muted f-12 m-t-10 mb-0">{{ __('catches::catches.feeding.catches_limit') }}</p>
</x-ui.card>
