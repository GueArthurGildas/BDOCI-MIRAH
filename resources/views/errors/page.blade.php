<x-layouts.auth :title="__('ui.errors.'.$code.'.title')">
    <div class="auth-box card text-center">
        <div class="card-body p-5">
            <h1 class="f-80 f-w-600 text-primary m-b-10">{{ $code }}</h1>
            <h4 class="m-b-15">{{ __("ui.errors.{$code}.title") }}</h4>
            <p class="text-muted m-b-30">{{ __("ui.errors.{$code}.message") }}</p>
            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="feather icon-home"></i> {{ __('ui.back_home') }}
            </a>
        </div>
    </div>
</x-layouts.auth>
