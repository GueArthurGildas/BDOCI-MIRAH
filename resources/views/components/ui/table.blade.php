@props(['paginator' => null])

<div class="table-responsive">
    <table {{ $attributes->class('table table-hover m-b-0') }}>
        <thead>
            <tr>{{ $head }}</tr>
            @isset($filters)
                <tr class="gs-filter-row">{{ $filters }}</tr>
            @endisset
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

@if ($paginator?->hasPages())
    <div class="card-footer d-flex justify-content-end">
        {{ $paginator->links() }}
    </div>
@endif
