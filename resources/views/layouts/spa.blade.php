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

    {{--@if (config('app.env') !== 'production')--}}
    {{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootswatch/4.0.0/solar/bootstrap.min.css">--}}
    {{--@endif--}}
</head>
<body>
    <main>
        @yield('content')
    </main>

    <script>
        window.application_config = {
            translations: <?php
            // copy all translations from /resources/lang/CURRENT_LOCALE/* to global JS variable
            $lang_files = File::files(resource_path() . '/lang/' . app()->getLocale());
            $trans = [];
            foreach ($lang_files as $f) {
                $filename = pathinfo($f)['filename'];
                $trans[$filename] = trans($filename);
            }
            echo json_encode($trans);
            ?>,
        };

        @guest
            window.current_user = null;
        @else
            window.current_user = {
            permissions: <?php
            echo json_encode(Auth::user()->getAllPermissions());
            ?>,
            name: <?php
            echo json_encode(Auth::user()->name);
            ?>
        };
        @endguest
    </script>
</body>
</html>