@props(['cancel'])

<div class="row">
    <div class="col-sm-9 offset-sm-3">
        <button type="submit" class="btn btn-primary">
            <i class="feather icon-save"></i> {{ __('ui.save') }}
        </button>
        <a href="{{ $cancel }}" class="btn btn-default m-l-10">{{ __('ui.cancel') }}</a>
    </div>
</div>
