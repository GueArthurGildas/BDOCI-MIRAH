@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="page-header">
    <div class="row align-items-end">
        <div class="col-lg-8">
            <div class="page-header-title">
                <div class="d-inline">
                    <h4>{{ $title }}</h4>
                    @if ($subtitle)
                        <span>{{ $subtitle }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="page-header-breadcrumb">
                <ul class="breadcrumb-title">
                    <li class="breadcrumb-item float-start">
                        <a href="{{ url('/') }}" title="{{ __('ui.home') }}"><i class="feather icon-home"></i></a>
                    </li>
                    @foreach ($breadcrumbs as $label => $url)
                        <li class="breadcrumb-item float-start">
                            <a href="{{ $url }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
