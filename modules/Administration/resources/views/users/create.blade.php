<x-layouts.app :title="__('administration::administration.users.create_title')">
    <x-ui.page-header :title="__('administration::administration.users.create_title')" :breadcrumbs="[__('administration::administration.users.title') => route('administration.users.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::administration.users.create_title')">
            <form method="POST" action="{{ route('administration.users.store') }}">
                @csrf
                @include('administration::users.form')
                <x-ui.form.actions :cancel="route('administration.users.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
