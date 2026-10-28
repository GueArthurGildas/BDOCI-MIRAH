<x-layouts.app :title="__('tracking::tracking.slots.title')">
    <x-ui.page-header :title="__('tracking::tracking.slots.title')" :subtitle="__('tracking::tracking.slots.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('tracking::tracking.slots.list_title')" flush>
            <x-slot:actions>
                @can(App\Support\Authorization\Permission::TrackingCreate->value)
                    <form method="POST" action="{{ route('tracking.slots.store') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-mini"><i class="feather icon-camera"></i> {{ __('tracking::tracking.slots.open') }}</button>
                    </form>
                @endcan
            </x-slot:actions>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('tracking::tracking.slots.period') }}</th>
                    <th>{{ __('tracking::tracking.slots.operator') }}</th>
                    <th>{{ __('tracking::tracking.slots.vessels') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($slots as $slot)
                    <tr>
                        <td class="text-nowrap">{{ $slot->started_at->isoFormat('L LT') }} → {{ $slot->ended_at->isoFormat('LT') }}</td>
                        <td>{{ $slot->operator?->name }}</td>
                        <td>{{ $slot->slot_positions_count }}</td>
                        <td class="text-end"><a href="{{ route('tracking.slots.show', $slot) }}" class="btn btn-outline-primary btn-mini">{{ __('tracking::tracking.slots.show') }}</a></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="4" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        {{ $slots->links() }}
    </div>
</x-layouts.app>
