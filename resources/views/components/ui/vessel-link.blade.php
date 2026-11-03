@props(['vessel', 'thumb' => true])

@php($url = App\Support\Presentation\VesselLink::url($vessel))

<span class="gs-vessel-cell">
    @if ($thumb)
        <img src="{{ $vessel->photoUrl() }}" class="gs-vessel-thumb" alt="" loading="lazy">
    @endif
    <span>
        @if ($url)
            <a href="{{ $url }}" {{ $attributes }}>{{ $vessel->name }}</a>
            @if (Route::has('vessels.quick'))
                <a href="{{ route('vessels.quick', $vessel) }}" class="gs-quick-link" data-gs-modal title="{{ __('vessels::vessels.quick.preview') }}"><i class="feather icon-eye"></i></a>
            @endif
        @else
            <span {{ $attributes }}>{{ $vessel->name }}</span>
        @endif
        <span class="d-block text-muted f-12">{{ $vessel->registration_number }}</span>
    </span>
</span>
