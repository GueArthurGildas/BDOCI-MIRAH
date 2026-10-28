<x-layouts.app :title="__('communications::ai_agent.title')">
    <x-ui.page-header :title="__('communications::ai_agent.title')" :subtitle="__('communications::ai_agent.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('communications::ai_agent.card_title')">
            <p class="text-muted">{{ __('communications::ai_agent.hint') }}</p>

            <x-ui.detail-list>
                <x-ui.detail :label="__('communications::ai_agent.account')">{{ $account?->email ?? __('communications::ai_agent.no_account') }}</x-ui.detail>
            </x-ui.detail-list>

            <form method="POST" action="{{ route('communications.ai-agent.update') }}">
                @csrf
                @method('PUT')
                <x-ui.form.checkbox name="enabled" :label="__('communications::ai_agent.enabled')" :checked="$enabled" />
                <x-ui.form.actions :cancel="route('administration.settings')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
