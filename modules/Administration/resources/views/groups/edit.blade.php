<x-layouts.app :title="__('administration::groups.edit_title')">
    <x-ui.page-header :title="$group->displayLabel()" :subtitle="__('administration::groups.edit_title')" :breadcrumbs="[__('administration::groups.title') => route('administration.groups.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::groups.edit_title')">
            <form method="POST" action="{{ route('administration.groups.update', $group) }}">
                @csrf
                @method('PUT')
                @include('administration::groups.form')
            </form>
        </x-ui.card>

        @unless ($isLocked ?? false)
            @include('administration::grants.panel', ['scopedGrants' => $scopedGrants, 'action' => route('administration.groups.grants.store', $group), 'actionOptions' => $actionOptions, 'zoneOptions' => $zoneOptions])
        @endunless
    </div>
</x-layouts.app>
