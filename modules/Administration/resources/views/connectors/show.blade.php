@php
    $tabLabels = [
        'errors' => __('administration::administration.connectors.tabs.errors').' ('.$dataSource->open_errors_count.')',
        'rejections' => __('administration::administration.connectors.tabs.rejections').' ('.$dataSource->pending_rejections_count.')',
        'syncs' => __('administration::administration.connectors.tabs.syncs'),
        'comments' => __('administration::administration.connectors.tabs.comments').' ('.$dataSource->comments_count.')',
    ];
@endphp

<x-layouts.app :title="$dataSource->name">
    <x-ui.page-header :title="$dataSource->name" :subtitle="__($dataSource->mode->label())" :breadcrumbs="[__('administration::administration.connectors.title') => route('administration.connectors')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <x-ui.stat-card :label="__('administration::administration.connectors.status')" :value="__($dataSource->indicator_status->label())" icon="icon-activity" :color="$dataSource->indicator_status->color()" compact />
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

        <x-ui.card :title="__('administration::administration.connectors.mode')">
            <x-ui.detail-list>
                <x-ui.detail :label="__('administration::administration.connectors.stale')">{{ $dataSource->is_stale ? __('administration::administration.connectors.stale') : __('administration::administration.connectors.fresh') }}</x-ui.detail>
                <x-ui.detail :label="__('administration::administration.connectors.last_error')">{{ $dataSource->last_error ?? '—' }}</x-ui.detail>
            </x-ui.detail-list>
            @can(App\Support\Authorization\Permission::ConnectorsManage->value)
                <form data-confirm="{{ __('administration::confirm.mode') }}" data-confirm-label="{{ __('administration::confirm.mode_yes') }}" method="POST" action="{{ route('administration.connectors.mode', $dataSource) }}" class="d-flex gap-2 m-t-10">
                    @csrf
                    @method('PATCH')
                    <select name="mode" class="form-select w-auto">
                        @foreach (App\Enums\DataSourceMode::options() as $value => $label)
                            <option value="{{ $value }}" @selected($dataSource->mode->value === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-default btn-sm">{{ __('administration::administration.connectors.change_mode') }}</button>
                </form>
            @endcan
            @can(App\Support\Authorization\Permission::ConnectorsSync->value)
                @if ($hasConnector)
                    <form data-confirm="{{ __('administration::confirm.sync') }}" data-confirm-label="{{ __('administration::confirm.sync_yes') }}" method="POST" action="{{ route('administration.connectors.sync', $dataSource) }}" enctype="multipart/form-data" class="d-flex gap-2 m-t-10">
                        @csrf
                        @if ($dataSource->mode === App\Enums\DataSourceMode::Import)
                            <input type="file" name="file" accept=".json" class="form-control w-auto" aria-label="{{ __('administration::administration.connectors.import_file') }}" required>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-refresh-cw"></i> {{ __('administration::administration.connectors.synchronise') }}</button>
                    </form>
                @else
                    <p class="text-muted m-t-10 m-b-0">{{ __('administration::administration.connectors.no_connector') }}</p>
                @endif
            @endcan
        </x-ui.card>

        <x-ui.tabs :tabs="$tabLabels" :active="$tab">
            @foreach (array_keys($tabLabels) as $name)
                <x-ui.tab-pane :id="$name" :active="$name === $tab">
                    @include('administration::connectors.tabs.'.$name)
                </x-ui.tab-pane>
            @endforeach
        </x-ui.tabs>
    </div>
</x-layouts.app>
