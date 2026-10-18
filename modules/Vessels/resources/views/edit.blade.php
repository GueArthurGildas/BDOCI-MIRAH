<x-layouts.app :title="__('vessels::vessels.edit_title')">
    <x-ui.page-header :title="__('vessels::vessels.edit_title')" :subtitle="$vessel->name" :breadcrumbs="[__('vessels::vessels.title') => route('vessels.index'), $vessel->name => route('vessels.show', $vessel)]" />

    <div class="page-body">
        <x-ui.card :title="$vessel->name">
            <form method="POST" action="{{ route('vessels.update', $vessel) }}">
                @csrf
                @method('PUT')
                @include('vessels::form')
                <x-ui.form.actions :cancel="route('vessels.show', $vessel)" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
