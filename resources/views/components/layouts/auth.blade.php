@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head', ['title' => $title])
</head>
<body class="fix-menu">
    @include('layouts.partials.preloader')

    <section class="login-block">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    {{ $slot }}

                    @include('layouts.partials.locale-links')
                </div>
            </div>
        </div>
    </section>
</body>
</html>
