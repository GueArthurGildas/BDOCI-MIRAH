<div class="gs-preview">
    @foreach (App\Enums\AlertCategory::cases() as $category)
        @php($alerts = $groups[$category->value])
        <div class="gs-preview-group">
            <div class="gs-preview-title">
                <i class="feather {{ $category->icon() }} text-{{ $category->color() }}"></i>
                <strong>{{ __($category->label()) }}</strong>
                <span class="gs-tree-count">{{ $counts[$category->value] }}</span>
            </div>
            @forelse ($alerts as $alert)
                <a href="{{ route('tracking.alerts.show', $alert) }}" class="gs-preview-item">
                    <span class="gs-preview-vessel">{{ $alert->vessel?->name }}</span>
                    <span class="gs-preview-meta">{{ __($alert->type->label()) }} · {{ $alert->triggered_at->diffForHumans() }}</span>
                </a>
            @empty
                <p class="gs-preview-empty">{{ __('tracking::tracking.alerts.preview_empty') }}</p>
            @endforelse
        </div>
    @endforeach
    <a href="{{ route('tracking.alerts.index') }}" class="gs-preview-all">{{ __('tracking::tracking.alerts.see_all') }} <i class="feather icon-arrow-right"></i></a>
</div>
