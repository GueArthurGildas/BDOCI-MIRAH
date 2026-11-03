@if ($photos->isEmpty())
    <div class="gs-photos gs-photos-empty">
        <img src="{{ asset('images/vessel-placeholder.svg') }}" class="gs-photo" alt="">
    </div>
@else
    <div id="vessel-photos" class="carousel slide gs-photos" data-gs-carousel>
        <div class="carousel-inner">
            @foreach ($photos as $photo)
                <div @class(['carousel-item', 'active' => $loop->first])>
                    <img src="{{ route('vessels.photos.show', [$vessel, $photo->id]) }}" class="gs-photo" alt="{{ $vessel->name }}" title="{{ __('vessels::vessels.photos.enlarge_hint') }}" data-gs-photo data-gs-photo-label="{{ $vessel->name }} ({{ $loop->iteration }}/{{ $loop->count }})">
                </div>
            @endforeach
        </div>
        @if ($photos->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#vessel-photos" data-bs-slide="prev" aria-label="{{ __('vessels::vessels.photos.previous') }}">
                <span class="gs-photo-arrow"><i class="feather icon-chevron-left"></i></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#vessel-photos" data-bs-slide="next" aria-label="{{ __('vessels::vessels.photos.next') }}">
                <span class="gs-photo-arrow"><i class="feather icon-chevron-right"></i></span>
            </button>
        @endif
    </div>
@endif
