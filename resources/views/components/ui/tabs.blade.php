@props(['tabs', 'active' => null])

@php($active ??= array_key_first($tabs))

<div class="tab-header card">
    <ul class="nav nav-tabs md-tabs tab-timeline" role="tablist">
        @foreach ($tabs as $id => $label)
            <li class="nav-item">
                <a @class(['nav-link', 'active' => $id === $active]) data-bs-toggle="tab" href="#{{ $id }}" role="tab">{{ $label }}</a>
                <div class="slide"></div>
            </li>
        @endforeach
    </ul>
</div>

<div class="tab-content">
    {{ $slot }}
</div>
