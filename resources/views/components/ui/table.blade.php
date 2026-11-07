@props(['paginator' => null, 'paginate' => 5])

<div class="table-responsive">
    <table {{ $attributes->class('table table-hover m-b-0') }} @if (! $paginator && $paginate) data-gs-paginate="{{ $paginate }}" @endif>
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
