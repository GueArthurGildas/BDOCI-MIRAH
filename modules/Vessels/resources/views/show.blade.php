<x-layouts.app :title="$vessel->name">
    <x-ui.page-header :title="$vessel->name" :subtitle="$vessel->registration_number" :breadcrumbs="[__('vessels::vessels.title') => route('vessels.index')]" />

    <div class="page-body">
        @include('vessels::show.header')

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
    </div>
</x-layouts.app>
