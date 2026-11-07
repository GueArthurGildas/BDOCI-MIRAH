@if ($photos->isEmpty())
    <p class="text-muted">{{ __('vessels::vessels.photos.none') }}</p>
@else
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
