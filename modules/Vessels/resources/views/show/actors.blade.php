<x-ui.card :title="__('vessels::vessels.tabs.actors')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.actors.fields.role') }}</th>
            <th>{{ __('vessels::vessels.actors.holder') }}</th>
            <th>{{ __('vessels::vessels.actors.fields.started_on') }}</th>
            <th>{{ __('vessels::vessels.actors.ended_on') }}</th>
        </x-slot:head>
        @forelse ($vessel->actors as $actor)
            <tr @class(['text-muted' => $actor->ended_on !== null])>
                <td>{{ __($actor->role->label()) }}</td>
                <td>{{ $actor->holderName() }}</td>
                <td>{{ $actor->started_on?->isoFormat('L') ?? '—' }}</td>
                <td>{{ $actor->ended_on?->isoFormat('L') ?? '—' }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>

@can(App\Support\Authorization\Permission::VesselsManage->value)
    <x-ui.card :title="__('vessels::vessels.actors.add')">
        <form method="POST" action="{{ route('vessels.actors.store', $vessel) }}">
            @csrf
            <x-ui.form.select name="role" :label="__('vessels::vessels.actors.fields.role')" :options="App\Enums\ActorRole::options()" required />
            <x-ui.form.select name="legal_entity_id" :label="__('vessels::vessels.actors.fields.legal_entity_id')" :options="$legalEntities" />
            <x-ui.form.select name="person_id" :label="__('vessels::vessels.actors.fields.person_id')" :options="$people" />
            <x-ui.form.input name="started_on" type="date" :label="__('vessels::vessels.actors.fields.started_on')" />
            <x-ui.form.actions :cancel="route('vessels.show', $vessel)" />
        </form>
    </x-ui.card>
@endcan

<x-ui.card :title="__('vessels::vessels.lists.title')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.lists.fields.external_list_id') }}</th>
            <th>{{ __('vessels::vessels.lists.institution') }}</th>
            <th>{{ __('vessels::vessels.lists.fields.listed_from') }}</th>
            <th>{{ __('vessels::vessels.lists.fields.listed_until') }}</th>
        </x-slot:head>
        @forelse ($vessel->externalLists as $list)
            <tr>
                <td>{{ $list->label }} <span class="text-muted f-12">({{ __($list->type->label()) }})</span></td>
                <td>{{ $list->institution->name }}</td>
                <td>{{ $list->pivot->listed_from ? \Illuminate\Support\Carbon::parse($list->pivot->listed_from)->isoFormat('L') : '—' }}</td>
                <td>{{ $list->pivot->listed_until ? \Illuminate\Support\Carbon::parse($list->pivot->listed_until)->isoFormat('L') : '—' }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>

@can(App\Support\Authorization\Permission::VesselsManage->value)
    <x-ui.card :title="__('vessels::vessels.lists.add')">
        <form method="POST" action="{{ route('vessels.external-lists.store', $vessel) }}">
            @csrf
            <x-ui.form.select name="external_list_id" :label="__('vessels::vessels.lists.fields.external_list_id')" :options="$externalLists" required />
            <x-ui.form.input name="listed_from" type="date" :label="__('vessels::vessels.lists.fields.listed_from')" />
            <x-ui.form.input name="listed_until" type="date" :label="__('vessels::vessels.lists.fields.listed_until')" />
            <x-ui.form.actions :cancel="route('vessels.show', $vessel)" />
        </form>
    </x-ui.card>
@endcan
