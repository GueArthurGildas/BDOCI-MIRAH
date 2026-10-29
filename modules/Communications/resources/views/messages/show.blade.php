<x-layouts.app :title="__('communications::messages.detail_title')">
    <x-ui.page-header :title="__('communications::messages.detail_title')" :breadcrumbs="[__('communications::communications.title') => route('communications.messages.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-7">
                <x-ui.card :title="$message->subject ?? __('communications::messages.no_subject')">
                    <p class="m-b-20 text-pre-line">{{ $message->body }}</p>

                    <x-ui.detail-list>
                        <x-ui.detail :label="__('communications::fields.status')"><x-ui.status-badge :status="$message->status" /></x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.channel')">{{ __($message->channel->code->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.recipient')">{{ $message->recipient }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.vessel_id')">
                            @if ($message->vessel)
                                <x-ui.vessel-link :vessel="$message->vessel" />
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.message_template_id')">{{ $message->template?->code }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.rule')">{{ $message->rule ? '#'.$message->rule->id : null }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.triggered_by')">{{ __($message->triggered_by->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.sender')">{{ $message->sender?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.sent_at')">{{ $message->sent_at?->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.acknowledged_at')">{{ $message->acknowledged_at?->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.reply_received')">{{ $message->reply_received ? __('ui.yes') : __('ui.no') }}</x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>
            </div>

            <div class="col-xl-5">
                @can(App\Support\Authorization\Permission::CommunicationsSend->value)
                    @if ($message->status === App\Enums\MessageStatus::Prepared)
                        <x-ui.card :title="__('communications::messages.validate_title')">
                            <p>{{ __('communications::messages.validate_hint') }}</p>
                            <form data-confirm="{{ __('communications::confirm.send') }}" data-confirm-label="{{ __('communications::confirm.send_yes') }}" data-confirm-details="{{ json_encode([['label' => __('communications::fields.channel'), 'value' => __($message->channel->code->label())], ['label' => __('communications::fields.recipient'), 'value' => (string) $message->recipient]], JSON_UNESCAPED_UNICODE) }}" method="POST" action="{{ route('communications.messages.send', $message) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary"><i class="feather icon-send"></i> {{ __('communications::messages.send') }}</button>
                            </form>
                        </x-ui.card>
                    @endif

                    @if ($message->status === App\Enums\MessageStatus::Sent)
                        <x-ui.card :title="__('communications::messages.acknowledge_title')">
                            <form method="POST" action="{{ route('communications.messages.acknowledge', $message) }}">
                                @csrf
                                <x-ui.form.checkbox name="reply_received" :label="__('communications::fields.reply_received')" />
                                <button type="submit" class="btn btn-primary"><i class="feather icon-check"></i> {{ __('communications::messages.acknowledge') }}</button>
                            </form>
                        </x-ui.card>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>
