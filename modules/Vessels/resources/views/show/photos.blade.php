@php($canManage = auth()->user()->can(App\Support\Authorization\Permission::VesselsManage->value))

<x-ui.card data-guide="photos" :title="__('vessels::vessels.photos.title')" :subtitle="trans_choice('vessels::vessels.photos.count', $photos->count(), ['count' => $photos->count()])">
    @if ($photos->isEmpty())
        <div class="gs-photos-empty">
            <img src="{{ asset('images/vessel-placeholder.svg') }}" alt="">
            <p class="text-muted mb-0">{{ __('vessels::vessels.photos.none') }}</p>
        </div>
    @else
        <div id="vessel-photos" class="carousel slide gs-photos" data-gs-carousel>
            <div class="carousel-inner">
                @foreach ($photos as $photo)
                    <div @class(['carousel-item', 'active' => $loop->first])>
                        <img src="{{ route('vessels.photos.show', [$vessel, $photo->id]) }}" class="gs-photo" alt="{{ $vessel->name }}" data-gs-photo data-gs-photo-label="{{ $vessel->name }} ({{ $loop->iteration }}/{{ $loop->count }})">
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
        <p class="text-muted f-12 text-center m-t-5 mb-0">{{ __('vessels::vessels.photos.enlarge_hint') }}</p>

        @if ($canManage)
            <ul class="gs-photo-strip">
                @foreach ($photos as $photo)
                    <li>
                        <img src="{{ route('vessels.photos.show', [$vessel, $photo->id]) }}" alt="">
                        <form method="POST" action="{{ route('vessels.photos.destroy', [$vessel, $photo->id]) }}" data-confirm="{{ __('vessels::confirm.remove_photo') }}" data-confirm-label="{{ __('vessels::confirm.remove_photo_yes') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="gs-photo-remove" title="{{ __('vessels::vessels.photos.remove') }}"><i class="feather icon-trash-2"></i></button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    @if ($canManage)
        <form method="POST" action="{{ route('vessels.photos.store', $vessel) }}" enctype="multipart/form-data" class="gs-photo-upload">
            @csrf
            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required class="form-control form-control-sm @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror">
            <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-upload"></i> {{ __('vessels::vessels.photos.add') }}</button>
            @error('photos')
                <div class="text-danger f-12 w-100">{{ $message }}</div>
            @enderror
            @error('photos.*')
                <div class="text-danger f-12 w-100">{{ $message }}</div>
            @enderror
        </form>
    @endif
</x-ui.card>

<div class="modal fade gs-modal" id="gs-photo-viewer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close gs-photo-close" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
                <img src="" alt="" class="gs-photo-large">
                <p class="gs-photo-caption mb-0" data-gs-photo-caption></p>
            </div>
        </div>
    </div>
</div>
