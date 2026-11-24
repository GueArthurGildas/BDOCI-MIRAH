<x-layouts.app :title="__('profile::profile.title')">
    <x-ui.page-header :title="__('profile::profile.title')" :subtitle="__('profile::profile.subtitle')" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-4">
                <x-ui.card :title="$user->fullName()" :subtitle="$user->job_title">
                    <div class="gs-profile-photo">
                        <img src="{{ $user->avatarUrl() ?? Vite::asset('resources/images/avatar-blank.jpg') }}" alt="{{ $user->fullName() }}">
                    </div>

                    <x-ui.detail-list>
                        <x-ui.detail :label="__('profile::profile.fields.first_name')">{{ $user->first_name ?: '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('profile::profile.fields.name')">{{ $user->name }}</x-ui.detail>
                        <x-ui.detail :label="__('profile::profile.fields.job_title')">{{ $user->job_title ?: '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('profile::profile.fields.role')">{{ $roles ? implode(', ', $roles) : '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('profile::profile.fields.team')">{{ $user->team?->name ?? '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('profile::profile.fields.email')">{{ $user->email }}</x-ui.detail>
                        <x-ui.detail :label="__('profile::profile.fields.phone')">{{ $user->phone ?: '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('profile::profile.fields.whatsapp_number')">{{ $user->whatsapp_number ?: '—' }}</x-ui.detail>
                    </x-ui.detail-list>

                    <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="gs-photo-upload">
                        @csrf
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required class="form-control form-control-sm @error('avatar') is-invalid @enderror">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-upload"></i> {{ __('profile::profile.photo.change') }}</button>
                        @error('avatar')
                            <div class="text-danger f-12 w-100">{{ $message }}</div>
                        @enderror
                    </form>
                    @if ($user->avatar_document_id)
                        <form method="POST" action="{{ route('profile.avatar.destroy') }}" class="m-t-10">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-default btn-sm"><i class="feather icon-trash-2"></i> {{ __('profile::profile.photo.remove') }}</button>
                        </form>
                    @endif
                </x-ui.card>
            </div>

            <div class="col-xl-8">
                @php
                    $kpis = [
                        'messages' => [$space['unreadMessages'], 'icon-mail', $space['unreadMessages'] > 0 ? 'warning' : 'success'],
                        'urgencies' => [$space['urgentAlerts']->count() + $space['urgentNotes']->count(), 'icon-alert-octagon', ($space['urgentAlerts']->count() + $space['urgentNotes']->count()) > 0 ? 'danger' : 'success'],
                        'alerts' => [array_sum($space['alertCounts']), 'icon-bell', 'info'],
                        'pending' => [$space['observations']->count() + $space['dueNotes']->count() + $space['operations']->count(), 'icon-clock', 'primary'],
                    ];
                @endphp
                <div class="row">
                    @foreach ($kpis as $key => [$count, $icon, $color])
                        <div class="col-xxl-3 col-md-6">
                            <x-ui.stat-card :label="__('profile::profile.cards.'.$key)" :value="Number::format($count)" :icon="$icon" :color="$color" compact>
                                <x-slot:actions>
                                    <button type="button" class="btn btn-default btn-sm" data-bs-toggle="modal" data-bs-target="#kpi-{{ $key }}">
                                        <i class="feather icon-list"></i> {{ __('profile::profile.recent.open', ['count' => Modules\Profile\Support\KpiRecent::LIMIT]) }}
                                    </button>
                                </x-slot:actions>
                            </x-ui.stat-card>
                        </div>
                    @endforeach
                </div>
                @foreach ($recent as $key => $items)
                    @include('profile::partials.kpi-modal', ['key' => $key, 'recent' => $items])
                @endforeach

                @include('profile::partials.messages', ['title' => __('profile::profile.messages.title'), 'notes' => $space['messages'], 'unread' => $space['unreadMessages']])
                @include('profile::partials.urgencies')
                @include('profile::partials.alerts')
                @include('profile::partials.pending')
            </div>
        </div>
    </div>
</x-layouts.app>
