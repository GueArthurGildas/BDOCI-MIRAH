<x-layouts.app :title="__('administration::custom_fields.edit_title')">
    <x-ui.page-header :title="$field->label" :subtitle="__('administration::custom_fields.edit_title')" :breadcrumbs="[__('administration::custom_fields.title') => route('administration.custom-fields.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::custom_fields.edit_title')">
            <form method="POST" action="{{ route('administration.custom-fields.update', $field) }}">
                @csrf
                @method('PUT')
                @include('administration::custom-fields.form')
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
