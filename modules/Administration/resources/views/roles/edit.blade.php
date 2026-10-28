<x-layouts.app :title="__('administration::administration.roles.edit_title')">
    <x-ui.page-header :title="App\Support\Authorization\Role::labelFor($role->name)" :subtitle="__('administration::administration.roles.edit_title')" :breadcrumbs="[__('administration::administration.roles.title') => route('administration.roles.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::administration.roles.edit_title')">
            <form method="POST" action="{{ route('administration.roles.update', $role) }}">
                @csrf
                @method('PUT')
                @include('administration::roles.form')
            </form>
        </x-ui.card>

        @unless ($isLocked ?? false)
            @include('administration::grants.panel', ['scopedGrants' => $scopedGrants, 'action' => route('administration.roles.grants.store', $role), 'actionOptions' => $actionOptions, 'zoneOptions' => $zoneOptions])
        @endunless
    </div>
</x-layouts.app>
