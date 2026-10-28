<x-layouts.app :title="__('communications::rules.title')">
    <x-ui.page-header :title="__('communications::rules.title')" :subtitle="__('communications::rules.subtitle')" />

    <div class="page-body">
        @include('communications::partials.tabs', ['active' => 'rules'])

        <x-ui.card :title="__('communications::rules.list_title')" flush>
            @can(App\Support\Authorization\Permission::CommunicationsManageDispatch->value)
                <x-slot:actions>
                    <a href="{{ route('communications.rules.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('communications::rules.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('communications::fields.trigger_event') }}</th>
                    <th>{{ __('communications::fields.message_template_id') }}</th>
                    <th>{{ __('communications::fields.recipient') }}</th>
                    <th>{{ __('communications::fields.frequency_minutes') }}</th>
                    <th>{{ __('communications::fields.daily_cap') }}</th>
                    <th>{{ __('communications::fields.stop_condition') }}</th>
                    <th>{{ __('communications::fields.mode') }}</th>
                    <th>{{ __('communications::fields.is_active') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($rules as $rule)
                    <tr>
                        <td class="f-w-600">{{ __("communications::triggers.{$rule->trigger_event}") }}</td>
                        <td>{{ $rule->template->code }}</td>
                        <td>{{ $rule->conditions['recipient'] ?? '' }}</td>
                        <td>{{ $rule->frequency_minutes }}</td>
                        <td>{{ $rule->daily_cap }}</td>
                        <td>{{ $rule->stop_condition ? __("communications::stop_conditions.{$rule->stop_condition}") : '' }}</td>
                        <td>{{ __($rule->mode->label()) }}</td>
                        <td><span class="label label-{{ $rule->is_active ? 'success' : 'default' }}">{{ $rule->is_active ? __('ui.yes') : __('ui.no') }}</span></td>
                        <td class="text-end">
                            @can(App\Support\Authorization\Permission::CommunicationsManageDispatch->value)
                                <a href="{{ route('communications.rules.edit', $rule) }}" class="btn btn-default btn-sm">{{ __('ui.edit') }}</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="9" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
