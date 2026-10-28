<x-ui.card :title="__('administration::grants.title')">
    <p class="text-muted">{{ __('administration::grants.help') }}</p>

    @if ($scopedGrants->isNotEmpty())
        <x-ui.table>
            <x-slot:head>
                <th>{{ __('administration::grants.action') }}</th>
                <th>{{ __('administration::grants.zone') }}</th>
                <th>{{ __('administration::grants.valid_from') }}</th>
                <th>{{ __('administration::grants.valid_to') }}</th>
                <th></th>
            </x-slot:head>
            @foreach ($scopedGrants as $grant)
                <tr>
                    <td>{{ __('administration::administration.permissions.'.$grant->action->code) }}</td>
                    <td>{{ $grant->zone?->name ?? '—' }}</td>
                    <td>{{ $grant->valid_from?->toDateString() ?? '—' }}</td>
                    <td>{{ $grant->valid_to?->toDateString() ?? '—' }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('administration.grants.destroy', $grant) }}" class="d-inline" onsubmit="return confirm(@js(__('administration::grants.confirm_remove')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('administration::grants.remove') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    @else
        <p class="text-muted">{{ __('administration::grants.none') }}</p>
    @endif

    <h6 class="m-t-20">{{ __('administration::grants.add') }}</h6>
    <form method="POST" action="{{ $action }}">
        @csrf
        <x-ui.form.select name="action" :label="__('administration::grants.action')" :options="$actionOptions" required />
        <x-ui.form.select name="zone_id" :label="__('administration::grants.zone')" :options="$zoneOptions" :placeholder="__('administration::grants.all_zones')" />
        <x-ui.form.input name="valid_from" type="date" :label="__('administration::grants.valid_from')" />
        <x-ui.form.input name="valid_to" type="date" :label="__('administration::grants.valid_to')" />
        <button type="submit" class="btn btn-primary">
            <i class="feather icon-plus"></i> {{ __('administration::grants.add_button') }}
        </button>
    </form>
</x-ui.card>
