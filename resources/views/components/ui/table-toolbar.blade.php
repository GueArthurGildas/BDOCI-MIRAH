@props(['id' => 'list-filters', 'action', 'paginator', 'exports' => null])

@php
    $options = collect(App\Support\Tables\PerPage::OPTIONS)->push($paginator->perPage())->unique()->sort()->values();
@endphp

<form method="GET" action="{{ $action }}" id="{{ $id }}" class="gs-table-toolbar">
    <div class="gs-table-toolbar-group">
        {{ $exports }}
    </div>

    <div class="gs-table-toolbar-group">
        <label class="gs-toolbar-field">
            {{ __('ui.per_page') }}
            <select name="per_page" class="form-select form-select-sm" data-auto-submit>
                @foreach ($options as $option)
                    <option value="{{ $option }}" @selected($option === $paginator->perPage())>{{ $option }}</option>
                @endforeach
            </select>
            {{ __('ui.per_page_entries') }}
        </label>

        {{ $slot }}

        @if (request()->except('page'))
            <a href="{{ $action }}" class="btn btn-default btn-sm">{{ __('ui.reset') }}</a>
        @endif
        <button type="submit" class="visually-hidden">{{ __('ui.filter') }}</button>
    </div>
</form>
