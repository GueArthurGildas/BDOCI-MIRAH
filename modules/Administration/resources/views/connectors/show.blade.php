@php
    $tabLabels = [
        'errors' => __('administration::administration.connectors.tabs.errors').' ('.$dataSource->open_errors_count.')',
        'rejections' => __('administration::administration.connectors.tabs.rejections').' ('.$dataSource->pending_rejections_count.')',
        'syncs' => __('administration::administration.connectors.tabs.syncs'),
        'comments' => __('administration::administration.connectors.tabs.comments').' ('.$dataSource->comments_count.')',
    ];
@endphp

<x-layouts.app :title="$dataSource->name">
    <x-ui.page-header :title="$dataSource->name" :subtitle="__($dataSource->kind->label())" :breadcrumbs="[__('administration::administration.connectors.title') => route('administration.connectors')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <x-ui.stat-card :label="__('administration::administration.connectors.status')" :value="__($dataSource->status->label())" icon="icon-activity" :color="$dataSource->status->color()" compact />
            </div>
            <div class="col-md-6 col-xl-3">
                <x-ui.stat-card :label="__('administration::administration.connectors.last_synced_at')" :value="$dataSource->last_synced_at?->diffForHumans() ?? __('administration::administration.connectors.never')" icon="icon-clock" color="info" compact />
            </div>
            <div class="col-md-6 col-xl-3">
                <x-ui.stat-card :label="__('administration::administration.connectors.open_errors')" :value="$dataSource->open_errors_count" icon="icon-alert-triangle" :color="$dataSource->open_errors_count > 0 ? 'danger' : 'success'" />
            </div>
            <div class="col-md-6 col-xl-3">
                <x-ui.stat-card :label="__('administration::administration.connectors.pending_rejections')" :value="$dataSource->pending_rejections_count" icon="icon-inbox" :color="$dataSource->pending_rejections_count > 0 ? 'warning' : 'success'" />
            </div>
        </div>

        <x-ui.tabs :tabs="$tabLabels" :active="$tab">
            @foreach (array_keys($tabLabels) as $name)
                <x-ui.tab-pane :id="$name" :active="$name === $tab">
                    @include('administration::connectors.tabs.'.$name)
                </x-ui.tab-pane>
            @endforeach
        </x-ui.tabs>
    </div>
</x-layouts.app>
