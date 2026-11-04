<x-layouts.app :title="__('communications::directory.title')">
    <x-ui.page-header :title="__('communications::directory.title')" :subtitle="__('communications::directory.subtitle')" />

    <div class="page-body">
        @include('communications::partials.tabs', ['active' => 'directory'])

        <x-ui.card :title="__('communications::directory.list_title')" :subtitle="trans_choice('ui.results_count', $contacts->total())" flush>
            <x-slot:actions>
                @can(App\Support\Authorization\Permission::RegistryManage->value)
                    <a href="{{ route('registry.create', 'contacts') }}" class="btn btn-default btn-sm"><i class="feather icon-plus"></i> {{ __('communications::directory.add_contact') }}</a>
                    <a href="{{ route('registry.list', 'contacts') }}" class="btn btn-default btn-sm"><i class="feather icon-edit"></i> {{ __('communications::directory.manage_contacts') }}</a>
                @endcan
            </x-slot:actions>

            <x-ui.table-toolbar :action="route('communications.directory.index')" :paginator="$contacts">
                <x-ui.toolbar-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('communications::directory.search_placeholder')" />
                <x-ui.toolbar-select name="category" :label="__('communications::directory.columns.category')" :options="$categories" :value="$filters['category'] ?? null" />
                <x-ui.toolbar-select name="country" :label="__('communications::directory.columns.country')" :options="$countries" :value="$filters['country'] ?? null" />
            </x-ui.table-toolbar>

            <form method="GET" action="{{ route('communications.directory.compose') }}">
                <x-ui.table :paginator="$contacts">
                    <x-slot:head>
                        <th style="width: 36px;">
                            @can(App\Support\Authorization\Permission::CommunicationsSend->value)
                                <input type="checkbox" data-gs-check-all aria-label="{{ __('communications::directory.select_all') }}">
                            @endcan
                        </th>
                        <th>{{ __('communications::directory.columns.organization') }}</th>
                        <th>{{ __('communications::directory.columns.category') }}</th>
                        <th>{{ __('communications::directory.columns.country') }}</th>
                        <th>{{ __('communications::directory.columns.email') }}</th>
                        <th>{{ __('communications::directory.columns.phone') }}</th>
                        <th></th>
                    </x-slot:head>
                    @forelse ($contacts as $contact)
                        <tr>
                            <td>
                                @can(App\Support\Authorization\Permission::CommunicationsSend->value)
                                    <input type="checkbox" name="contacts[]" value="{{ $contact->id }}" aria-label="{{ $contact->organization }}">
                                @endcan
                            </td>
                            <td class="f-w-600">
                                {{ $contact->organization ?: '—' }}
                                @if ($contact->institution)
                                    <div class="text-muted f-12">{{ $contact->institution->name }}</div>
                                @endif
                            </td>
                            <td>{{ $contact->category ?: '—' }}</td>
                            <td>{{ $contact->country?->label ?? '—' }}</td>
                            <td>{{ $contact->email ?: '—' }}</td>
                            <td class="text-nowrap">{{ $contact->phone ?: '—' }}</td>
                            <td class="text-end">
                                @can(App\Support\Authorization\Permission::CommunicationsSend->value)
                                    <a href="{{ route('communications.directory.compose', ['contacts' => [$contact->id]]) }}" class="btn btn-outline-primary btn-mini"><i class="feather icon-send"></i> {{ __('communications::directory.notify') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-ui.empty-row colspan="7" />
                    @endforelse
                </x-ui.table>

                @can(App\Support\Authorization\Permission::CommunicationsSend->value)
                    @if ($contacts->isNotEmpty())
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-send"></i> {{ __('communications::directory.notify_selection') }}</button>
                        </div>
                    @endif
                @endcan
            </form>
        </x-ui.card>

        <x-ui.card :title="__('communications::directory.how_title')">
            <p class="mb-0">{{ __('communications::directory.how_text') }}</p>
        </x-ui.card>
    </div>
</x-layouts.app>
