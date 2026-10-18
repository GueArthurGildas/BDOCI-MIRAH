@props(['vessel'])

@php($url = App\Support\Presentation\VesselLink::url($vessel))

@if ($url)
    <a href="{{ $url }}" {{ $attributes }}>{{ $vessel->name }}</a>
@else
    <span {{ $attributes }}>{{ $vessel->name }}</span>
@endif
<div class="text-muted f-12">{{ $vessel->registration_number }}</div>
