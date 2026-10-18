@props(['paginator' => null])

<div class="table-responsive">
    <table {{ $attributes->class('table table-hover m-b-0') }}>
        <thead>
            <tr>{{ $head }}</tr>
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
