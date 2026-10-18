<x-layouts.app :title="__('administration::administration.connectors.title')">
    <x-ui.page-header :title="__('administration::administration.connectors.title')" :subtitle="__('administration::administration.connectors.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach ($dataSources as $source)
                <div class="col-xl-4 col-md-6">
                    <x-ui.card :title="$source->name" :subtitle="__($source->kind->label())">
                        <x-slot:actions>
                            <x-ui.status-badge :status="$source->status" />
                        </x-slot:actions>

                        <x-ui.detail-list>
                            <x-ui.detail :label="__('administration::administration.connectors.last_synced_at')">
                                {{ $source->last_synced_at?->isoFormat('L LT') ?? __('administration::administration.connectors.never') }}
                                @if ($source->last_synced_at)
                                    <div class="text-muted f-12">{{ $source->last_synced_at->diffForHumans() }}</div>
                                @endif
                            </x-ui.detail>
                            <x-ui.detail :label="__('administration::administration.connectors.errors')">
                                <span @class(['f-w-600', 'text-danger' => $source->error_count > 0])>{{ $source->error_count }}</span>
                            </x-ui.detail>
                            <x-ui.detail :label="__('administration::administration.connectors.rejected')">
                                <span @class(['f-w-600', 'text-warning' => $source->rejected_count > 0])>{{ $source->rejected_count }}</span>
                            </x-ui.detail>
                        </x-ui.detail-list>
                    </x-ui.card>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
