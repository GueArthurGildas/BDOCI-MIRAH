<x-layouts.app :title="__('Dashboard')">
    <x-page-header :title="__('Dashboard')" :subtitle="__('Overview of your activity')" />

    <div class="page-body">
        <div class="row">
            @foreach ($stats as $stat)
                <div class="col-xl-3 col-md-6">
                    <x-stat-card
                        :label="$stat['label']"
                        :value="Number::format($stat['value'])"
                        :icon="$stat['icon']"
                        :color="$stat['color']"
                        :footer="__('Updated at :time', ['time' => now()->format('H:i')])"
                    />
                </div>
            @endforeach

            <div class="col-12">
                <x-card :title="__('Latest users')" flush>
                    <div class="table-responsive">
                        <table class="table table-hover m-b-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Registered') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestUsers as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if ($user->email_verified_at)
                                                <span class="label label-success">{{ __('Verified') }}</span>
                                            @else
                                                <span class="label label-warning">{{ __('Pending') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $user->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">{{ __('No users yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
