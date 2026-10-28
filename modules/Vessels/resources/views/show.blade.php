@php($tabs = ['identity', 'identifiers', 'actors', 'sources', 'licenses', 'devices', 'movements', 'observations', 'inspections', 'infractions', 'catches', 'missions', 'timeline'])

<x-layouts.app :title="$vessel->name">
    <x-ui.page-header :title="$vessel->name" :subtitle="$vessel->registration_number" :breadcrumbs="[__('vessels::vessels.title') => route('vessels.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-4">
                @include('vessels::show.summary')
            </div>
            <div class="col-xl-8">
                @include('vessels::show.position')
            </div>
        </div>

        <x-ui.tabs :tabs="collect($tabs)->mapWithKeys(fn ($tab) => [$tab => __('vessels::vessels.tabs.'.$tab)])->all()">
            @foreach ($tabs as $tab)
                <x-ui.tab-pane :id="$tab" :active="$loop->first">
                    @include('vessels::show.'.$tab)
                </x-ui.tab-pane>
            @endforeach
        </x-ui.tabs>
    </div>
</x-layouts.app>
