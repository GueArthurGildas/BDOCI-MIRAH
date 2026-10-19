<x-layouts.auth :title="__('auth::login.title')">
    <form method="POST" action="{{ route('login') }}" class="md-float-material form-material">
        @csrf

        <div class="text-center">
            <img class="login-logo" src="{{ Vite::asset('resources/images/logo.png') }}" alt="{{ config('app.name') }}">
        </div>

        <div class="auth-box card">
            <div class="card-body">
                <div class="row m-b-20">
                    <div class="col-md-12">
                        <h3 class="text-center">{{ __('auth::login.title') }}</h3>
                    </div>
                </div>

                <div class="mb-3 form-primary">
                    <input type="email" name="email" value="{{ old('email') }}" @class(['form-control', 'is-invalid' => $errors->has('email')]) placeholder="{{ __('auth::login.email_placeholder') }}" required autofocus autocomplete="username">
                    <span class="form-bar"></span>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-primary">
                    <input type="password" name="password" @class(['form-control', 'is-invalid' => $errors->has('password')]) placeholder="{{ __('auth::login.password_placeholder') }}" required autocomplete="current-password">
                    <span class="form-bar"></span>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row m-t-25 text-start">
                    <div class="col-12">
                        <div class="checkbox-fade fade-in-primary">
                            <label class="form-label">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                <span class="text-inverse">{{ __('auth::login.remember_me') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row m-t-30">
                    <div class="col-md-12">
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-md waves-effect waves-light text-center m-b-20">
                                {{ __('auth::login.submit') }}
                            </button>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-10">
                        <p class="text-inverse text-start m-b-0">{{ __('auth::login.thank_you') }}</p>
                        <p class="text-inverse text-start"><b class="f-w-600">{{ config('app.name') }}</b></p>
                    </div>
                    <div class="col-md-2">
                        <img class="login-logo-small" src="{{ Vite::asset('resources/images/logo.png') }}" alt="{{ config('app.name') }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-layouts.auth>
