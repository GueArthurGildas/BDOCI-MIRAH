<x-layouts.app :title="__('administration::administration.roles.create_title')">
    <x-ui.page-header :title="__('administration::administration.roles.create_title')" :breadcrumbs="[__('administration::administration.roles.title') => route('administration.roles.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::administration.roles.create_title')">
            <form method="POST" action="{{ route('administration.roles.store') }}">
                @csrf
                @include('administration::roles.form')
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
