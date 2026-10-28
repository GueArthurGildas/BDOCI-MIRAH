<x-layouts.app :title="__('communications::channels.title')">
    <x-ui.page-header :title="__('communications::channels.title')" :subtitle="__('communications::channels.subtitle')" />

    <div class="page-body">
        @include('communications::partials.tabs', ['active' => 'channels'])

        @foreach ($channels as $channel)
            <x-ui.card :title="__($channel->code->label())">
                @can(App\Support\Authorization\Permission::CommunicationsManageDispatch->value)
                    <form method="POST" action="{{ route('communications.channels.update', $channel) }}">
                        @csrf
                        @method('PUT')
                        <x-ui.form.checkbox :name="'is_auto_dispatch_enabled'" :label="__('communications::fields.is_auto_dispatch_enabled')" :checked="$channel->is_auto_dispatch_enabled" />
                        <x-ui.form.checkbox :name="'is_consent_required'" :label="__('communications::fields.is_consent_required')" :checked="$channel->is_consent_required" />
                        <x-ui.form.actions :cancel="route('communications.channels.index')" />
                    </form>
                @else
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('communications::fields.is_auto_dispatch_enabled')">{{ $channel->is_auto_dispatch_enabled ? __('ui.yes') : __('ui.no') }}</x-ui.detail>
                        <x-ui.detail :label="__('communications::fields.is_consent_required')">{{ $channel->is_consent_required ? __('ui.yes') : __('ui.no') }}</x-ui.detail>
                    </x-ui.detail-list>
                @endcan
            </x-ui.card>
        @endforeach

        <x-ui.card :title="__('communications::channels.connector_title')" :subtitle="__('communications::channels.connector_hint')">
            @can(App\Support\Authorization\Permission::ConnectorsManage->value)
                <form method="POST" action="{{ route('communications.channels.connector') }}">
                    @csrf
                    @method('PUT')
                    <x-ui.form.input name="supplier" :label="__('communications::fields.supplier')" :value="$connector->supplier" maxlength="80" required />
                    <x-ui.form.input name="domain" :label="__('communications::fields.domain')" :value="$connector->domain" maxlength="190" />
                    <x-ui.form.select name="mailer" :label="__('communications::fields.mailer')" :options="$mailers" :value="$connector->parameters['mailer'] ?? null" />
                    <x-ui.form.checkbox name="is_active" :label="__('communications::fields.is_active')" :checked="$connector->is_active" />
                    <x-ui.form.actions :cancel="route('communications.channels.index')" />
                </form>
            @else
                <x-ui.detail-list>
                    <x-ui.detail :label="__('communications::fields.supplier')">{{ $connector->supplier }}</x-ui.detail>
                    <x-ui.detail :label="__('communications::fields.domain')">{{ $connector->domain }}</x-ui.detail>
                    <x-ui.detail :label="__('communications::fields.is_active')">{{ $connector->is_active ? __('ui.yes') : __('ui.no') }}</x-ui.detail>
                </x-ui.detail-list>
            @endcan
        </x-ui.card>

        <x-ui.card :title="__('communications::channels.consents_title')" :subtitle="__('communications::channels.consents_hint')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('communications::fields.number') }}</th>
                    <th>{{ __('communications::fields.label') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($consents as $consent)
                    <tr>
                        <td class="f-w-600">{{ $consent->code }}</td>
                        <td>{{ $consent->label }}</td>
                        <td class="text-end">
                            @can(App\Support\Authorization\Permission::CommunicationsSend->value)
                                <form method="POST" action="{{ route('communications.channels.consents.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="number" value="{{ $consent->code }}">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('communications::channels.revoke') }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="3" />
                @endforelse
            </x-ui.table>

            @can(App\Support\Authorization\Permission::CommunicationsSend->value)
                <div class="p-20">
                    <form method="POST" action="{{ route('communications.channels.consents.store') }}">
                        @csrf
                        <x-ui.form.input name="number" :label="__('communications::fields.number')" maxlength="20" required />
                        <x-ui.form.input name="label" :label="__('communications::fields.label')" maxlength="160" />
                        <x-ui.form.actions :cancel="route('communications.channels.index')" />
                    </form>
                </div>
            @endcan
        </x-ui.card>
    </div>
</x-layouts.app>
