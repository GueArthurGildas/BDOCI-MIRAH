@props(['direction'])

<span class="text-nowrap">
    <span class="gs-state-icon is-{{ $direction->color() }}"><i class="feather {{ $direction->icon() }}"></i></span>
    <x-ui.status-badge :status="$direction" />
</span>
