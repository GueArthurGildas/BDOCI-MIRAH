<x-layouts.app :title="__('notifications::notifications.title')">
    <x-ui.page-header :title="__('notifications::notifications.title')" :subtitle="__('notifications::notifications.subtitle')"/>

    <x-ui.card :title="__('notifications::notifications.list_title')" :subtitle="trans_choice('notifications::notifications.unread_count', $unreadCount)" :flush="true">
        <x-slot:actions>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="feather icon-check"></i> {{ __('notifications::notifications.mark_all_read') }}
                    </button>
                </form>
            @endif
        </x-slot:actions>

        @forelse ($notifications as $notification)
            <a href="{{ $notification['url'] }}" @class(['gs-notice', 'is-'.$notification['level'], 'is-unread' => ! $notification['read']])>
                <span class="gs-notice-icon"><i class="feather {{ $notification['icon'] }}"></i></span>
                <span class="gs-notice-body">
                    <strong>{{ $notification['title'] }}</strong>
                    <span>{{ $notification['message'] }}</span>
                </span>
                <small>{{ $notification['time'] }}</small>
            </a>
        @empty
            <p class="text-muted m-0 p-4">{{ __('notifications::notifications.empty') }}</p>
        @endforelse

        @if ($notifications->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $notifications->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
