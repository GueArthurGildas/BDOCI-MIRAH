<x-layouts.app :title="__('administration::administration.settings.title')">
    <x-ui.page-header :title="__('administration::administration.settings.title')" :subtitle="__('administration::administration.settings.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('administration::administration.settings.thresholds')">
            <form method="POST" action="{{ route('administration.settings.update') }}">
                @csrf
                @method('PUT')

                @foreach ($keys as $key)
                    <x-ui.form.input
                        :name="$key->value"
                        :label="__($key->label())"
                        :value="$values[$key->value]"
                        :type="$key->type() === 'time' ? 'time' : 'number'"
                        step="any"
                    />
                @endforeach

                <x-ui.form.actions :cancel="route('administration.settings.edit')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
