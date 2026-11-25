@props(['action', 'compact' => false])

@php($form = 'delete-'.md5($action))

@if ($compact)
    <details {{ $attributes->class('gs-delete') }}>
        <summary class="btn btn-mini btn-outline-danger" title="{{ __('ui.deletion.button') }}"><i class="feather icon-trash-2"></i></summary>
        <div class="gs-delete-panel">
            @include('components.ui.partials.delete-fields', ['action' => $action, 'form' => $form])
        </div>
    </details>
@else
    <div {{ $attributes->class('gs-delete-inline') }}>
        @include('components.ui.partials.delete-fields', ['action' => $action, 'form' => $form])
    </div>
@endif
