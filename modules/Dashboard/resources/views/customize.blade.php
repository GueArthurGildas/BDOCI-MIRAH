<div class="gs-quick">
    <div class="gs-quick-head">
        <span class="gs-modal-icon"><i class="feather icon-sliders"></i></span>
        <div>
            <h5>{{ __('dashboard::dashboard.customize.title') }}</h5>
            <small>{{ __('dashboard::dashboard.customize.hint') }}</small>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
    </div>

    <form id="dashboard-customize" method="POST" action="{{ route('dashboard.widgets.update') }}">
        @csrf
        @method('PUT')
        <div class="gs-customize-list">
            @foreach ($kinds as $kind)
                @if (isset($groups[$kind]))
                    <h6>{{ __('dashboard::widgets.groups.'.$kind) }}</h6>
                    @foreach ($groups[$kind] as $widget)
                        <div class="checkbox-fade fade-in-primary">
                            <label class="form-label">
                                <input type="checkbox" name="widgets[]" value="{{ $widget->key }}" @checked(in_array($widget->key, $selected, true))>
                                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                <span><strong>{{ $widget->title() }}</strong><br><small class="text-muted">{{ $widget->explanation() }}</small></span>
                            </label>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </form>

    <form id="dashboard-reset" method="POST" action="{{ route('dashboard.widgets.reset') }}">
        @csrf
        @method('DELETE')
    </form>

    <div class="d-flex justify-content-between mt-3">
        <button type="submit" form="dashboard-reset" class="btn btn-default">{{ __('dashboard::dashboard.customize.reset') }}</button>
        <button type="submit" form="dashboard-customize" class="btn btn-primary">{{ __('ui.save') }}</button>
    </div>
</div>
