<x-layouts.app :title="__('communications::templates.title')">
    <x-ui.page-header :title="__('communications::templates.title')" :subtitle="__('communications::templates.subtitle')" />

    <div class="page-body">
        @include('communications::partials.tabs', ['active' => 'templates'])

        <x-ui.card :title="__('communications::templates.list_title')" flush>
            @can(App\Support\Authorization\Permission::CommunicationsManageTemplates->value)
                <x-slot:actions>
                    <a href="{{ route('communications.templates.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('communications::templates.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('communications::fields.code') }}</th>
                    <th>{{ __('communications::fields.channel') }}</th>
                    <th>{{ __('communications::fields.language') }}</th>
                    <th>{{ __('communications::fields.subject') }}</th>
                    <th>{{ __('communications::fields.form') }}</th>
                    <th>{{ __('communications::fields.is_active') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($templates as $template)
                    <tr>
                        <td class="f-w-600">{{ $template->code }}</td>
                        <td>{{ __($template->channel->label()) }}</td>
                        <td>{{ __($template->language->label()) }}</td>
                        <td>{{ $template->subject }}</td>
                        <td>{{ __($template->form->label()) }}</td>
                        <td><span class="label label-{{ $template->is_active ? 'success' : 'default' }}">{{ $template->is_active ? __('ui.yes') : __('ui.no') }}</span></td>
                        <td class="text-end">
                            @can(App\Support\Authorization\Permission::CommunicationsManageTemplates->value)
                                <a href="{{ route('communications.templates.edit', $template) }}" class="btn btn-default btn-sm">{{ __('ui.edit') }}</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <x-ui.card :title="__('communications::templates.variables_title')">
            <p class="m-0">{{ __('communications::templates.variables_hint') }}</p>
            <code>{vessel_name} {registration_number} {call_sign} {owner} {date} {sender}</code>
        </x-ui.card>
    </div>
</x-layouts.app>
