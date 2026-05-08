<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>{{ __('messages.admin') }} | Re-authenticate</title>
    <link rel="icon" href="{{ asset(settings('favicon', 'images/favicon.png')) }}">
    <link href="{{ admin_asset('css/tabler.min.css?1692870487') }}" rel="stylesheet"/>
    <link href="{{ admin_asset('css/admin.css') }}" rel="stylesheet"/>
    @livewireStyles
</head>
<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            @livewire(admin_view_path('auth.livewire.reauthenticate'))
        </div>
    </div>

    @livewireScripts
</body>
</html>
