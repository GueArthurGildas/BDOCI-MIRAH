<x-layouts.app :title="__('registry::registry.title')">
    <x-ui.page-header :title="__('registry::registry.title')" :subtitle="__('registry::registry.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach ($resources as $key => $entry)
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('registry.list', $key) }}">
                        <x-ui.stat-card :label="__('registry::registry.resources.'.$key.'.title')" :value="$entry['count']" :icon="$entry['resource']->icon" color="info" />
                    </a>
                </div>
            @endforeach
            @can(App\Support\Authorization\Permission::RegistryManage->value)
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('registry.priorities.edit') }}">
                        <x-ui.stat-card :label="__('registry::registry.priorities.title')" :value="count(config('registry.tracked_vessel_fields'))" icon="icon-sliders" color="warning" />
                    </a>
                </div>
            @endcan
        </div>
    </div>
</x-layouts.app>
