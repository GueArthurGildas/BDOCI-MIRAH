<x-ui.card :title="__('vessels::vessels.tabs.identifiers')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.identifiers.type') }}</th>
            <th>{{ __('vessels::vessels.identifiers.value') }}</th>
            <th>{{ __('vessels::vessels.identifiers.valid_from') }}</th>
            <th>{{ __('vessels::vessels.identifiers.valid_to') }}</th>
        </x-slot:head>
        @forelse ($vessel->identifiers as $identifier)
            <tr @class(['text-muted' => $identifier->valid_to !== null])>
                <td>{{ __($identifier->type->label()) }}</td>
                <td>{{ $identifier->value }}</td>
                <td>{{ $identifier->valid_from?->isoFormat('L') ?? '—' }}</td>
                <td>{{ $identifier->valid_to?->isoFormat('L') ?? '—' }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>

@can(App\Support\Authorization\Permission::VesselsManage->value)
    <x-ui.card :title="__('vessels::vessels.identifiers.add')">
        <form method="POST" action="{{ route('vessels.identifiers.store', $vessel) }}">
            @csrf
            <x-ui.form.select name="type" :label="__('vessels::vessels.identifiers.type')" :options="App\Enums\IdentifierType::options()" required />
            <x-ui.form.input name="value" :label="__('vessels::vessels.identifiers.value')" required />
            <x-ui.form.input name="valid_from" type="date" :label="__('vessels::vessels.identifiers.valid_from')" />
            <x-ui.form.actions :cancel="route('vessels.show', $vessel)" />
        </form>
    </x-ui.card>
@endcan

<x-ui.card :title="__('vessels::vessels.history.title')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.history.changed_on') }}</th>
            <th>{{ __('vessels::vessels.history.former_name') }}</th>
            <th>{{ __('vessels::vessels.history.former_flag') }}</th>
        </x-slot:head>
        @forelse ($vessel->history as $entry)
            <tr>
                <td>{{ $entry->changed_on?->isoFormat('L') ?? '—' }}</td>
                <td>{{ $entry->former_name ?? '—' }}</td>
                <td>{{ $entry->formerFlagCountry?->label ?? '—' }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="3" />
        @endforelse
    </x-ui.table>
</x-ui.card>
