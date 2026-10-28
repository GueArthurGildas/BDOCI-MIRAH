<x-layouts.app :title="__('administration::groups.create_title')">
    <x-ui.page-header :title="__('administration::groups.create_title')" :breadcrumbs="[__('administration::groups.title') => route('administration.groups.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::groups.create_title')">
            <form method="POST" action="{{ route('administration.groups.store') }}">
                @csrf
                @include('administration::groups.form')
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
