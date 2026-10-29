<div class="modal fade gs-modal" id="gs-confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <span class="gs-modal-icon"><i class="feather icon-help-circle"></i></span>
                <h5 data-confirm-title>{{ __('ui.confirm.title') }}</h5>
                <p data-confirm-message></p>
                <div class="gs-modal-details" data-confirm-details></div>
                <div class="gs-modal-actions">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="button" class="btn btn-primary" data-confirm-accept>{{ __('ui.confirm.proceed') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
