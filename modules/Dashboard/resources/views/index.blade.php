<x-layouts.app :title="__('dashboard::dashboard.title')">
    <x-ui.page-header :title="__('dashboard::dashboard.title')" :subtitle="__('dashboard::dashboard.subtitle')">
        <x-slot:actions>
            <a href="{{ route('dashboard.customize') }}" data-gs-modal class="btn btn-sm btn-outline-primary">
                <i class="feather icon-sliders"></i> {{ __('dashboard::dashboard.customize.button') }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="page-body">
        @if ($kpis->isNotEmpty())
            <div class="row">
                @foreach ($kpis as $data)
                    <div class="col-xl-3 col-md-6">
                        @include('dashboard::widgets.kpi')
                    </div>
                @endforeach
            </div>
        @endif

        @include('dashboard::widgets.messages')

        @if ($charts->isNotEmpty())
            <div class="row">
                @foreach ($charts as $data)
                    <div class="col-xl-6">
                        @include('dashboard::widgets.chart')
                    </div>
                @endforeach
            </div>
        @endif

        @foreach ($tables as $data)
            @include('dashboard::widgets.'.$data->widget->key)
        @endforeach

        @if ($kpis->isEmpty() && $charts->isEmpty() && $tables->isEmpty())
            <x-ui.card :title="__('dashboard::dashboard.empty.title')">
                <p class="mb-0">{{ __('dashboard::dashboard.empty.text') }}</p>
            </x-ui.card>
        @endif
    </div>
</x-layouts.app>
