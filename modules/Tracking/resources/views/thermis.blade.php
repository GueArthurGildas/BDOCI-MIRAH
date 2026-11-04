<x-layouts.app :title="__('tracking::thermis.title')">
    <x-ui.page-header :title="__('tracking::thermis.title')" :subtitle="__('tracking::thermis.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('tracking::thermis.open_title')">
            @include('tracking::partials.thermis', ['panel' => $panel, 'action' => route('thermis.index'), 'tab' => null])
        </x-ui.card>

        <x-ui.card :title="__('tracking::thermis.why_title')">
            <p class="mb-0">{{ __('tracking::thermis.why') }}</p>
        </x-ui.card>
    </div>
</x-layouts.app>
