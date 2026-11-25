<form id="{{ $form }}" method="POST" action="{{ $action }}" data-confirm="{{ __('ui.deletion.confirm') }}" data-confirm-title="{{ __('ui.deletion.title') }}" data-confirm-label="{{ __('ui.confirm.delete') }}">
    @csrf
    @method('DELETE')
    <label for="{{ $form }}-reason" class="form-label f-12 mb-1">{{ __('ui.deletion.reason') }}</label>
    <div class="input-group input-group-sm">
        <input type="text" id="{{ $form }}-reason" name="reason" class="form-control" required maxlength="500" placeholder="{{ __('ui.deletion.reason_placeholder') }}">
        <button type="submit" class="btn btn-danger"><i class="feather icon-trash-2"></i> {{ __('ui.deletion.button') }}</button>
    </div>
    <small class="text-muted d-block mt-1">{{ __('ui.deletion.hint') }}</small>
</form>
