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
        },
        active: function() {
            sessionStorage.fonts = true;
        }
    });
</script>
<script>
    WebFont.load({
        google: {
            "families": ["Lato:300,400,700,900"]
        },
        custom: {
            "families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands",
                "simple-line-icons"
            ],
            urls: ['{{ asset('dashboard/css/fonts.min.css') }}']
        },
        active: function() {
            sessionStorage.fonts = true;
        }
    });
</script>


<!-- CSS Files -->
<link rel="stylesheet" href="{{ asset('/dashboard/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('/dashboard/css/atlantis.css') }}">

<!-- CSS Just for demo purpose, don't include it in your project -->
<link rel="stylesheet" href="{{ asset('/dashboard/css/demo.css') }}">
<link rel="stylesheet" href="{{ asset('/dashboard/css/style.css') }}">

{{-- icon --}}
<link rel="stylesheet" href="{{ asset('/dashboard/icon/themify-icons/themify-icons.css') }}">
<link rel="stylesheet" href="{{ asset('/dashboard/icon/icofont/css/icofont.css') }}">
<link rel="stylesheet" href="{{ asset('/dashboard/icon/ion-icon/css/ionicons.min.css') }}">

{{-- responsive data table --}}
<link rel="stylesheet" href="{{ asset('/dashboard/vendor/datatable-responsive/css/responsive.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('/dashboard/vendor/datatable-responsive/css/responsive.bootstrap.min.css') }}">
{{-- global style --}}
<style>
    .wrap-text {
        max-width: 400px;
        word-wrap: break-word;
        white-space: normal;
    }
</style>
