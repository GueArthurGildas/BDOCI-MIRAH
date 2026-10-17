<x-layouts.app :title="__('dashboard::dashboard.title')">
    <x-ui.page-header :title="__('dashboard::dashboard.title')" :subtitle="__('dashboard::dashboard.subtitle')" />

    <div class="page-body">
        <div class="row">
            @foreach ($stats as $stat)
                <div class="col-xl-3 col-md-6">
                    <x-ui.stat-card
                        :label="$stat['label']"
                        :value="Number::format($stat['value'])"
                        :icon="$stat['icon']"
                        :color="$stat['color']"
                        :footer="__('ui.updated_at', ['time' => now()->format('H:i')])"
                    />
                </div>
            @endforeach

            <div class="col-12">
                <x-ui.card :title="__('dashboard::dashboard.latest_users.title')" flush>
                    <div class="table-responsive">
                        <table class="table table-hover m-b-0">
                            <thead>
                                <tr>
                                    <th>{{ __('dashboard::dashboard.latest_users.name') }}</th>
                                    <th>{{ __('dashboard::dashboard.latest_users.email') }}</th>
                                    <th>{{ __('dashboard::dashboard.latest_users.status') }}</th>
                                    <th class="text-end">{{ __('dashboard::dashboard.latest_users.registered') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestUsers as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if ($user->email_verified_at)
                                                <span class="label label-success">{{ __('dashboard::dashboard.latest_users.verified') }}</span>
                                            @else
                                                <span class="label label-warning">{{ __('dashboard::dashboard.latest_users.pending') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $user->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">{{ __('dashboard::dashboard.latest_users.empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
