@props(['status'])

@php
    $descriptionKey = 'enums.descriptions.'.Illuminate\Support\Str::snake(class_basename($status)).'.'.$status->value;
    $description = Illuminate\Support\Facades\Lang::has($descriptionKey) ? __($descriptionKey) : null;
@endphp

<span {{ $attributes->class(['label', 'label-'.$status->color()]) }} @if ($description) data-bs-toggle="tooltip" data-bs-title="{{ $description }}" @endif>{{ __($status->label()) }}</span>
