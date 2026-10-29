@props(['action'])

<x-ui.card data-guide="filter_card" :title="__('ui.filters')">
    <form method="GET" action="{{ $action }}" class="row g-3 align-items-end">
        {{ $slot }}

        <div class="col-md-auto ms-auto">
            <a href="{{ $action }}" class="btn btn-default btn-sm">{{ __('ui.reset') }}</a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="feather icon-filter"></i> {{ __('ui.filter') }}
            </button>
        </div>
    </form>
</x-ui.card>
