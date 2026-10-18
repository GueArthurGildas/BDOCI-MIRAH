<x-layouts.app :title="__('vessels::vessels.create_title')">
    <x-ui.page-header :title="__('vessels::vessels.create_title')" :breadcrumbs="[__('vessels::vessels.title') => route('vessels.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('vessels::vessels.create_title')">
            <form method="POST" action="{{ route('vessels.store') }}">
                @csrf
                @include('vessels::form')
                <x-ui.form.actions :cancel="route('vessels.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
