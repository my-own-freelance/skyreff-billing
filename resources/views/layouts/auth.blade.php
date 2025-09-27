@php
    $config = \App\Models\WebConfig::first();
    $webLogo =
        $config && $config->web_logo
            ? url('/') . Storage::url($config->web_logo)
            : asset('skyreff-logo.jpeg');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title')</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <link rel="icon" href="{{ $webLogo }}" type="image/x-icon" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <!-- Fonts and icons -->
    <script src="{{ asset('dashboard/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: {
                "families": ["Lato:300,400,700,900"]
            },
            custom: {
                "families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands",
                    "simple-line-icons"
                ],
                urls: ["{{ asset('dashboard/css/fonts.min.css') }}"]
            },
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('dashboard/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/css/atlantis.css') }}">
</head>

<body class="login">
    <div class="wrapper wrapper-login wrapper-login-full p-0">
        @yield('content')

    </div>
    <script src="{{ asset('dashboard/js/core/jquery.3.2.1.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/atlantis.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    {{-- custom alert --}}
    <script src="{{ asset('dashboard/js/alert.js') }}"></script>
    @stack('scripts')
</body>

</html>
