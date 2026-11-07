<x-ui.card data-guide="photos" :title="__('vessels::vessels.photos.title')" :subtitle="trans_choice('vessels::vessels.photos.count', $photos->count(), ['count' => $photos->count()])">
    @can(App\Support\Authorization\Permission::VesselsManage->value)
        @include('vessels::show._photo-manager')
    @else
        <ul class="gs-photo-strip">
            @foreach ($photos as $photo)
                <li><img src="{{ route('vessels.photos.show', [$vessel, $photo->id]) }}" alt="" data-gs-photo data-gs-photo-label="{{ $vessel->name }} ({{ $loop->iteration }}/{{ $loop->count }})"></li>
            @endforeach
        </ul>
        @if ($photos->isEmpty())
            <p class="text-muted mb-0">{{ __('vessels::vessels.photos.none') }}</p>
        @endif
    @endcan
</x-ui.card>
