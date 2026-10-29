@if (session('status') && session('popup'))
    <div class="modal fade gs-modal gs-modal-success" id="gs-popup" tabindex="-1" aria-hidden="true" data-autoshow>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <span class="gs-modal-icon"><i class="feather icon-check"></i></span>
                    <h5>{{ __('ui.popup.done') }}</h5>
                    <p>{{ session('status') }}</p>
                    @if (is_array(session('popup')))
                        <div class="gs-modal-details">
                            @foreach (session('popup') as $detail)
                                <div><small>{{ $detail['label'] }}</small><strong>{{ $detail['value'] }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                    <div class="gs-modal-actions">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">{{ __('ui.popup.continue') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
