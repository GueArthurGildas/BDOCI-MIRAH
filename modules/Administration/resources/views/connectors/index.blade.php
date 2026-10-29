<x-layouts.app :title="__('administration::administration.connectors.title')">
    <x-ui.page-header :title="__('administration::administration.connectors.title')" :subtitle="__('administration::administration.connectors.subtitle')" />

    <div class="page-body">
        @can(App\Support\Authorization\Permission::ConnectorsManage->value)
            <p><a href="{{ route('administration.connectors.links') }}" class="btn btn-default btn-sm"><i class="feather icon-link"></i> {{ __('administration::administration.connectors.external_links') }}</a></p>
        @endcan
        <div class="row">
            @foreach ($dataSources as $source)
                <div class="col-xl-4 col-md-6">
                    <x-ui.card data-guide="source" :title="$source->name" :subtitle="__($source->mode->label())">
                        <x-slot:actions>
                            @if ($source->is_stale)<span class="label label-warning">{{ __('administration::administration.connectors.stale') }}</span>@endif
                            <x-ui.status-badge :status="$source->indicator_status" />
                        </x-slot:actions>

                        <x-ui.detail-list>
                            <x-ui.detail :label="__('administration::administration.connectors.last_synced_at')">
                                {{ $source->last_synced_at?->isoFormat('L LT') ?? __('administration::administration.connectors.never') }}
                                @if ($source->last_synced_at)
                                    <div class="text-muted f-12">{{ $source->last_synced_at->diffForHumans() }}</div>
                                @endif
                            </x-ui.detail>
                            <x-ui.detail :label="__('administration::administration.connectors.open_errors')">
                                <span @class(['f-w-600', 'text-danger' => $source->open_errors_count > 0])>{{ $source->open_errors_count }}</span>
                            </x-ui.detail>
                            <x-ui.detail :label="__('administration::administration.connectors.pending_rejections')">
                                <span @class(['f-w-600', 'text-warning' => $source->pending_rejections_count > 0])>{{ $source->pending_rejections_count }}</span>
                            </x-ui.detail>
                            <x-ui.detail :label="__('administration::administration.connectors.tabs.comments')">
                                {{ $source->comments_count }}
                            </x-ui.detail>
                        </x-ui.detail-list>

                        <a href="{{ route('administration.connectors.show', $source) }}" class="btn btn-primary btn-sm m-t-10">
                            <i class="feather icon-eye"></i> {{ __('administration::administration.connectors.details') }}
                        </a>
                    </x-ui.card>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
