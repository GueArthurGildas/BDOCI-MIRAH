<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ? "$title | " : '' }}{{ config('app.name') }}</title>
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
@vite(['resources/scss/app.scss', 'resources/js/app.js'])
