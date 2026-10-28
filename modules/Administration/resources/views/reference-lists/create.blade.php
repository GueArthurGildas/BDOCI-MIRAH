<x-layouts.app :title="__('administration::reference_lists.create_title')">
    <x-ui.page-header :title="__('administration::reference_lists.create_title')" :breadcrumbs="[__('administration::reference_lists.title') => route('administration.reference-lists.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::reference_lists.create_title')">
            <form method="POST" action="{{ route('administration.reference-lists.store') }}">
                @csrf
                <x-ui.form.input name="code" :label="__('administration::reference_lists.code')" maxlength="60" required />
                <x-ui.form.input name="label" :label="__('administration::reference_lists.label')" maxlength="120" required />
                <p class="text-muted f-12">{{ __('administration::reference_lists.code_hint') }}</p>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> {{ __('ui.save') }}</button>
                    <a href="{{ route('administration.reference-lists.index') }}" class="btn btn-default m-l-10">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
