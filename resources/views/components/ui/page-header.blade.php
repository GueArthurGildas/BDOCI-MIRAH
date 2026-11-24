@props(['title', 'subtitle' => null, 'breadcrumbs' => [], 'icon' => null, 'actions' => null, 'back' => null])
@inject('navigation', App\Support\Navigation\Navigation::class)
@php($icon ??= $navigation->activeItemFor(auth()->user())?->icon ?? 'icon-file-text')
@php($back ??= collect($breadcrumbs)->last())

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="page-header-title">
                @if ($back)
                    <a href="{{ $back }}" class="btn btn-default btn-sm gs-back" data-gs-back data-bs-toggle="tooltip" data-bs-title="{{ __('ui.back') }}" aria-label="{{ __('ui.back') }}"><i class="feather icon-arrow-left"></i></a>
                @endif
                <span class="gs-page-icon"><i class="feather {{ $icon }}"></i></span>
                <div>
                    <h4>{{ $title }}</h4>
                    @if ($subtitle)
                        <span>{{ $subtitle }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @if ($actions)
                <div class="gs-header-actions">{{ $actions }}</div>
            @endif
            <ul class="breadcrumb-title">
                <li class="breadcrumb-item">
                    <a href="{{ url('/') }}" title="{{ __('ui.home') }}"><i class="feather icon-home"></i></a>
                </li>
                @foreach ($breadcrumbs as $label => $url)
                    <li class="breadcrumb-item">
                        <a href="{{ $url }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
