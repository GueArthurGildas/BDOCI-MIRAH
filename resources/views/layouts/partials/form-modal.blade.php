<div class="modal fade gs-modal gs-form-modal" id="gs-form-modal" data-form-error="{{ __('ui.form_error') }}" data-form-failed="{{ __('ui.form_failed') }}" tabindex="-1" aria-hidden="true" aria-labelledby="gs-form-modal-title">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gs-form-modal-title" data-form-title></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
            </div>
            <div class="modal-body" data-form-body></div>
            <div class="modal-footer">
                <small class="text-muted me-auto"><span class="gs-required-mark">*</span> {{ __('ui.required_fields') }}</small>
                <a href="#" class="btn btn-link btn-sm" data-form-page>{{ __('ui.open_full_page') }}</a>
            </div>
        </div>
    </div>
</div>
