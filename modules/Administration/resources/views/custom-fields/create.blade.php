<x-layouts.app :title="__('administration::custom_fields.create_title')">
    <x-ui.page-header :title="__('administration::custom_fields.create_title')" :breadcrumbs="[__('administration::custom_fields.title') => route('administration.custom-fields.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::custom_fields.create_title')">
            <form method="POST" action="{{ route('administration.custom-fields.store') }}">
                @csrf
                @include('administration::custom-fields.form')
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
