<x-layouts.app :title="__('watch::watch.observations.title')">
    <x-ui.page-header :title="__('watch::watch.observations.title')" :subtitle="__('watch::watch.observations.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('watch::watch.observations.list_title')" :subtitle="trans_choice('ui.results_count', $observations->total())" flush>
            @can(App\Support\Authorization\Permission::WatchCreate->value)
                <x-slot:actions>
                    <a href="{{ route('watch.observations.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('watch::watch.observations.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table :paginator="$observations">
                <x-slot:head>
                    <th>{{ __('watch::watch.fields.created_at') }}</th>
                    <th>{{ __('watch::watch.fields.vessel') }}</th>
                    <th>{{ __('watch::watch.fields.body') }}</th>
                    <th>{{ __('watch::watch.fields.author') }}</th>
                    <th>{{ __('watch::watch.fields.status') }}</th>
                </x-slot:head>
                @forelse ($observations as $observation)
                    <tr>
                        <td class="text-nowrap">{{ $observation->created_at->isoFormat('L LT') }}</td>
                        <td><x-ui.vessel-link :vessel="$observation->vessel" /></td>
                        <td class="text-wrap">{{ Str::limit($observation->body, 90) }}</td>
                        <td>{{ $observation->author?->name }}</td>
                        <td class="text-nowrap">
                            @can(App\Support\Authorization\Permission::WatchCreate->value)
                                <form method="POST" action="{{ route('watch.observations.update', $observation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach (App\Enums\SurveillanceObservationStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected($observation->status === $status)>{{ __($status->label()) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <x-ui.status-badge :status="$observation->status" />
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
