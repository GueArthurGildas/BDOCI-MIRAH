<x-layouts.app :title="__('communications::messages.new')">
    <x-ui.page-header :title="__('communications::messages.new')" :breadcrumbs="[__('communications::communications.title') => route('communications.messages.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('communications::messages.new')">
            <form data-confirm="{{ __('communications::confirm.send_new') }}" data-confirm-label="{{ __('communications::confirm.send_new_yes') }}" method="POST" action="{{ route('communications.messages.store') }}">
                @csrf
                @include('communications::partials.choice-buttons', ['name' => 'channel', 'label' => __('communications::fields.channel'), 'options' => App\Enums\CommunicationChannelCode::options(), 'selected' => 'email', 'required' => true])
                <x-ui.form.select name="message_template_id" :label="__('communications::fields.message_template_id')" :options="$templates" required />
                <x-ui.form.select name="vessel_id" :label="__('communications::fields.vessel_id')" :options="$vessels" :value="$selectedVessel" />
                <x-ui.form.input name="recipient" :label="__('communications::fields.recipient')" maxlength="190" required />
                <x-ui.form.actions :cancel="route('communications.messages.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
