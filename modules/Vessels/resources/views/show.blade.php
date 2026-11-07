<x-layouts.app :title="$vessel->name">
    <x-ui.page-header :title="$vessel->name" :subtitle="$vessel->registration_number" :breadcrumbs="[__('vessels::vessels.title') => route('vessels.index')]">
        <x-slot:actions>
            <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('vessels::vessels.views.label') }}">
                <a href="{{ route('vessels.show', [$vessel, 'view' => 'tabs']) }}" @class(['btn', 'btn-primary' => $view === 'tabs', 'btn-default' => $view !== 'tabs'])>
                    <i class="feather icon-layout"></i> {{ __('vessels::vessels.views.tabs') }}
                </a>
                <a href="{{ route('vessels.show', [$vessel, 'view' => 'folders']) }}" @class(['btn', 'btn-primary' => $view === 'folders', 'btn-default' => $view !== 'folders'])>
                    <i class="feather icon-folder"></i> {{ __('vessels::vessels.views.folders') }}
                </a>
            </div>
            <a href="{{ route('vessels.sheet', $vessel) }}" class="btn btn-sm btn-outline-primary m-1">
                <i class="feather icon-download"></i> {{ __('vessels::vessels.sheet.download') }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="page-body">
        @include('vessels::show.header')

        @if ($view === 'folders')
            <div class="row">
                <div class="col-xl-3">
                    @include('vessels::show.tree')
                </div>
                <div class="col-xl-9">
                    @include('vessels::show.'.$pane)
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-xl-4">
                    @include('vessels::show.summary')
                </div>
                <div class="col-xl-8">
                    @include('vessels::show.position')
                </div>
            </div>

            <x-ui.tabs :tabs="collect($tabs)->mapWithKeys(fn ($tab) => [$tab => __('vessels::vessels.tabs.'.$tab)])->all()" :active="$activeTab && in_array($activeTab, $tabs) ? $activeTab : null">
                @foreach ($tabs as $tab)
                    <x-ui.tab-pane :id="$tab" :active="$activeTab && in_array($activeTab, $tabs) ? $tab === $activeTab : $loop->first">
                        @include('vessels::show.'.$tab)
                    </x-ui.tab-pane>
                @endforeach
            </x-ui.tabs>
        @endif
    </div>
</x-layouts.app>
