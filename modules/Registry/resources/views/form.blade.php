@php($exists = $record->exists)
@php($title = __('registry::registry.'.($exists ? 'edit_title' : 'create_title'), ['resource' => __('registry::registry.resources.'.$resource->key.'.singular')]))

<x-layouts.app :title="$title">
    <x-ui.page-header :title="$title" :breadcrumbs="[__('registry::registry.title') => route('registry.index'), __('registry::registry.resources.'.$resource->key.'.title') => route('registry.list', $resource->key)]" />

    <div class="page-body">
        <x-ui.card :title="$title">
            <form method="POST" action="{{ $exists ? route('registry.update', [$resource->key, $record->getKey()]) : route('registry.store', $resource->key) }}">
                @csrf
                @if ($exists)
                    @method('PUT')
                @endif

                @foreach ($resource->fields as $field)
                    @php($label = __('registry::registry.fields.'.$field->name))
                    @if ($field->type === 'select')
                        <x-ui.form.select :name="$field->name" :label="$label" :options="$field->optionList()" :value="$record->{$field->name}" :required="$field->required" />
                    @elseif ($field->type === 'checkbox')
                        <x-ui.form.checkbox :name="$field->name" :label="$label" :checked="(bool) $record->{$field->name}" />
                    @elseif ($field->type === 'textarea')
                        <x-ui.form.textarea :name="$field->name" :label="$label" :value="$record->{$field->name}" />
                    @elseif ($field->type === 'date')
                        <x-ui.form.input :name="$field->name" type="date" :label="$label" :value="$record->{$field->name}?->format('Y-m-d')" />
                    @elseif (str_starts_with($field->type, 'number'))
                        <x-ui.form.input :name="$field->name" type="number" :step="substr($field->type, 7)" :label="$label" :value="$record->{$field->name}" :required="$field->required" />
                    @else
                        <x-ui.form.input :name="$field->name" :type="$field->type" :label="$label" :value="$record->{$field->name}" :required="$field->required" />
                    @endif
                @endforeach

                <x-ui.form.actions :cancel="route('registry.list', $resource->key)" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
