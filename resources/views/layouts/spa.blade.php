<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kiosk Management System') }}</title>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

</head>
<body class="sidebar-mini">
    <main>
        @yield('content')
    </main>

    <script>
        @guest
            window.current_user = null;
        @else
            window.current_user = {
                permissions: <?php
                    echo json_encode(Auth::user()->getAllPermissions()->map(function ($permission) { return ['name' => $permission->name]; }));
                ?>,
                roles: <?php
                    echo json_encode(Auth::user()->roles->map(function ($role) { return ['name' => $role->name]; }));
                ?>,
                name: <?php
                    echo json_encode(Auth::user()->name);
                ?>,
                id: <?php
                    echo json_encode(Auth::user()->id);
                ?>
            };
        @endguest

        window.env = '{{ config('app.env') }}';
        window.sentry_dsn = '{{ config('sentry.dsn') }}';
    </script>
</body>
</html>