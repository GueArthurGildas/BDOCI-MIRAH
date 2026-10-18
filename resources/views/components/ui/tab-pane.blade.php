@props(['id', 'active' => false])

<div @class(['tab-pane', 'active' => $active]) id="{{ $id }}" role="tabpanel">
    {{ $slot }}
</div>
