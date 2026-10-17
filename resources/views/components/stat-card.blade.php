@props(['label', 'value', 'icon', 'color' => 'yellow', 'footer' => null])

<div class="card bg-c-{{ $color }} update-card">
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-8">
                <h4 class="text-white">{{ $value }}</h4>
                <h6 class="text-white m-b-0">{{ $label }}</h6>
            </div>
            <div class="col-4 text-end">
                <i class="feather {{ $icon }} text-white f-40"></i>
            </div>
        </div>
    </div>
    @if ($footer)
        <div class="card-footer">
            <p class="text-white m-b-0">
                <i class="feather icon-clock text-white f-14 m-r-10"></i>{{ $footer }}
            </p>
        </div>
    @endif
</div>
