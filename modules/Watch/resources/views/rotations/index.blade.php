<x-layouts.app :title="__('watch::watch.rotations.title')">
    <x-ui.page-header :title="__('watch::watch.rotations.title')" :subtitle="__('watch::watch.rotations.subtitle')" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('watch::watch.rotations.list_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('watch::watch.fields.day') }}</th>
                            <th>{{ __('watch::watch.fields.team') }}</th>
                            <th>{{ __('watch::watch.fields.lead') }}</th>
                            <th>{{ __('watch::watch.fields.hours') }}</th>
                            <th>{{ __('watch::watch.fields.handover') }}</th>
                        </x-slot:head>
                        @forelse ($rotations as $rotation)
                            <tr>
                                <td class="text-nowrap">{{ $rotation->day->isoFormat('dddd L') }}</td>
                                <td>{{ $rotation->team->name }}</td>
                                <td>{{ $rotation->team->lead?->name }}</td>
                                <td class="text-nowrap">{{ substr($rotation->start_time, 0, 5) }} - {{ substr($rotation->end_time, 0, 5) }}</td>
                                <td>
                                    @if ($rotation->handovers->isNotEmpty())
                                        <span class="label label-success">{{ __('watch::watch.rotations.handover_done') }}</span>
                                    @else
                                        @can(App\Support\Authorization\Permission::WatchCreate->value)
                                            <form data-confirm="{{ __('watch::confirm.handover') }}" data-confirm-label="{{ __('watch::confirm.handover_yes') }}" method="POST" action="{{ route('watch.rotations.handover', $rotation) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-primary btn-mini">{{ __('watch::watch.rotations.start_handover') }}</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                @can(App\Support\Authorization\Permission::WatchManage->value)
                    <x-ui.card :title="__('watch::watch.rotations.plan_title')" :subtitle="__('watch::watch.rotations.plan_hint', ['teams' => config('surveillance.watch.teams_count')])">
                        <form method="POST" action="{{ route('watch.rotations.plan') }}">
                            @csrf
                            <x-ui.form.input name="start_on" type="date" :label="__('watch::watch.fields.start_on')" :value="today()->addDay()->toDateString()" required />
                            <x-ui.form.input name="days_count" type="number" min="1" max="62" :label="__('watch::watch.fields.days_count')" value="14" required />
                            <x-ui.form.actions :cancel="route('watch.rotations.index')" />
                        </form>
                    </x-ui.card>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>
