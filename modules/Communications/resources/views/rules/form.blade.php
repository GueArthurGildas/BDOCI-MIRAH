@php($title = $rule->exists ? __('communications::rules.edit_title') : __('communications::rules.new'))

<x-layouts.app :title="$title">
    <x-ui.page-header :title="$title" :breadcrumbs="[__('communications::rules.title') => route('communications.rules.index')]" />

    <div class="page-body">
        <x-ui.card :title="$title">
            <form method="POST" action="{{ $rule->exists ? route('communications.rules.update', $rule) : route('communications.rules.store') }}">
                @csrf
                @if ($rule->exists)
                    @method('PUT')
                @endif
                <x-ui.form.select name="trigger_event" :label="__('communications::fields.trigger_event')" :options="Modules\Communications\Support\TriggerEvents::options()" :value="$rule->trigger_event" required />
                <x-ui.form.select name="message_template_id" :label="__('communications::fields.message_template_id')" :options="$templates" :value="$rule->message_template_id" required />
                <x-ui.form.input name="recipient" :label="__('communications::fields.recipient')" :value="$rule->conditions['recipient'] ?? null" maxlength="190" required />
                <x-ui.form.input name="frequency_minutes" type="number" min="1" :label="__('communications::fields.frequency_minutes')" :value="$rule->frequency_minutes" />
                <x-ui.form.input name="daily_cap" type="number" min="1" :label="__('communications::fields.daily_cap')" :value="$rule->daily_cap" />
                <x-ui.form.select name="stop_condition" :label="__('communications::fields.stop_condition')" :options="Modules\Communications\Support\StopConditions::options()" :value="$rule->stop_condition" />
                <x-ui.form.select name="mode" :label="__('communications::fields.mode')" :options="App\Enums\DispatchMode::options()" :value="$rule->mode" required />
                <x-ui.form.select name="editable_by_permission_group_id" :label="__('communications::fields.editable_by_permission_group_id')" :options="$groups" :value="$rule->editable_by_permission_group_id" />
                <x-ui.form.checkbox name="is_active" :label="__('communications::fields.is_active')" :checked="$rule->is_active" />
                <x-ui.form.actions :cancel="route('communications.rules.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
