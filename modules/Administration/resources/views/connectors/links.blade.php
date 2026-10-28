<x-layouts.app :title="__('administration::administration.connectors.links.title')">
    <x-ui.page-header :title="__('administration::administration.connectors.links.title')" :subtitle="__('administration::administration.connectors.links.subtitle')" :breadcrumbs="[__('administration::administration.connectors.title') => route('administration.connectors')]" />

    <div class="page-body">
        <form method="POST" action="{{ route('administration.connectors.links.update') }}">
            @csrf
            @method('PUT')
            @foreach ($sources as $source)
                <x-ui.card :title="__($source->label())">
                    <x-ui.form.input :name="'links['.$source->value.'][model_url]'" :label="__('administration::administration.connectors.links.model_url')" :value="$links[$source->value]->model_url ?? null" />
                    <x-ui.form.checkbox :name="'links['.$source->value.'][is_copy_fallback]'" :label="__('administration::administration.connectors.links.is_copy_fallback')" :checked="$links[$source->value]->is_copy_fallback ?? true" />
                </x-ui.card>
            @endforeach
            <x-ui.form.actions :cancel="route('administration.connectors')" />
        </form>
    </div>
</x-layouts.app>
