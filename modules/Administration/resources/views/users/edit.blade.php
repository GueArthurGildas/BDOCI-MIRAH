<x-layouts.app :title="__('administration::administration.users.edit_title')">
    <x-ui.page-header :title="__('administration::administration.users.edit_title')" :subtitle="$user->name" :breadcrumbs="[__('administration::administration.users.title') => route('administration.users.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="$user->name">
                    <form method="POST" action="{{ route('administration.users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        @include('administration::users.form')
                        <x-ui.form.actions :cancel="route('administration.users.index')" />
                    </form>
                </x-ui.card>
            </div>
            <div class="col-xl-4">
                <x-ui.audit-history :subject="$user" />
            </div>
        </div>
    </div>
</x-layouts.app>
