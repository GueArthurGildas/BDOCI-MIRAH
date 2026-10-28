@php($title = $template->exists ? __('communications::templates.edit_title') : __('communications::templates.new'))

<x-layouts.app :title="$title">
    <x-ui.page-header :title="$title" :breadcrumbs="[__('communications::templates.title') => route('communications.templates.index')]" />

    <div class="page-body">
        <x-ui.card :title="$title">
            <form method="POST" action="{{ $template->exists ? route('communications.templates.update', $template) : route('communications.templates.store') }}">
                @csrf
                @if ($template->exists)
                    @method('PUT')
                @endif
                <x-ui.form.input name="code" :label="__('communications::fields.code')" :value="$template->code" maxlength="60" required />
                <x-ui.form.select name="channel" :label="__('communications::fields.channel')" :options="Arr::only(App\Enums\CommunicationChannelCode::options(), ['email', 'whatsapp'])" :value="$template->channel" required />
                <x-ui.form.select name="language" :label="__('communications::fields.language')" :options="App\Enums\MessageLanguage::options()" :value="$template->language" required />
                <x-ui.form.input name="subject" :label="__('communications::fields.subject')" :value="$template->subject" maxlength="255" />
                <x-ui.form.textarea name="body" :label="__('communications::fields.body')" :value="$template->body" rows="8" required />
                <x-ui.form.select name="form" :label="__('communications::fields.form')" :options="App\Enums\MessageForm::options()" :value="$template->form" required />
                <x-ui.form.checkbox name="is_active" :label="__('communications::fields.is_active')" :checked="$template->is_active" />
                <x-ui.form.actions :cancel="route('communications.templates.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
