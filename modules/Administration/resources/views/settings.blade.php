<x-layouts.app :title="__('administration::settings.title')">
    <x-ui.page-header :title="__('administration::settings.title')" :subtitle="__('administration::settings.subtitle')" />

    <div class="page-body">
        <form method="POST" action="{{ route('administration.settings.update') }}">
            @csrf
            @method('PUT')

            @foreach ($groups as $group => $definitions)
                <x-ui.card data-guide="group" :title="__('administration::settings.groups.'.$group.'.title')" :subtitle="__('administration::settings.groups.'.$group.'.description')">
                    @foreach ($definitions as $field => $definition)
                        @include('administration::settings.field', ['field' => $field, 'definition' => $definition, 'settings' => $settings])
                    @endforeach
                </x-ui.card>
            @endforeach

            <div class="d-flex justify-content-between mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="feather icon-save"></i> {{ __('ui.save') }}
                </button>
                <button type="submit" form="reset-settings" class="btn btn-default">
                    <i class="feather icon-rotate-ccw"></i> {{ __('administration::settings.reset') }}
                </button>
            </div>
        </form>

        <form id="reset-settings" method="POST" action="{{ route('administration.settings.reset') }}">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-layouts.app>
