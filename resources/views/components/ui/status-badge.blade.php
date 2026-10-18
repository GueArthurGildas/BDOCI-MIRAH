@props(['status'])

<span class="label label-{{ $status->color() }}">{{ __($status->label()) }}</span>
