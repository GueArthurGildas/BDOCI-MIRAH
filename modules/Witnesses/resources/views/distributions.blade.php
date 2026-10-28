<x-layouts.app :title="__('witnesses::distributions.title')">
    <x-ui.page-header :title="__('witnesses::distributions.title')" :subtitle="__('witnesses::distributions.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('witnesses::distributions.list_title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('witnesses::distributions.fields.sent_at') }}</th>
                    <th>{{ __('witnesses::distributions.fields.recipient') }}</th>
                    <th>{{ __('witnesses::distributions.fields.version') }}</th>
                </x-slot:head>
                @forelse ($distributions as $distribution)
                    <tr>
                        <td class="text-nowrap">{{ $distribution->sent_at->isoFormat('L LT') }}</td>
                        <td>{{ $distribution->recipient }}</td>
                        <td>v{{ $distribution->publication->version }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="3" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <x-ui.card :title="__('witnesses::distributions.record')">
            <form method="POST" action="{{ route('witnesses.distributions.store') }}">
                @csrf
                <x-ui.form.select name="authorized_list_publication_id" :label="__('witnesses::distributions.fields.version')" :options="$publications" required />
                <x-ui.form.input name="recipient" :label="__('witnesses::distributions.fields.recipient')" required />
                <x-ui.form.actions :cancel="route('witnesses.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
