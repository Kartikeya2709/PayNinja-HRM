<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>@yield('title') &mdash; PayNinja - HRM</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- jQuery 3.6.0 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://www.unpkg.com/olamaps-web-sdk@latest/dist/olamaps-web-sdk.umd.js"></script>
    
    <!-- Google Maps API will be loaded by the component when needed -->
    @stack('google-maps')

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/menu-search.css') }}">

    <!-- Daterange picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <!-- Additional CSS (if any) -->
    @stack('css')

    <!-- Custom JavaScript -->
    <script src="{{ asset('js/sidebar.js') }}"></script>
</head>
<body>
    <div id="app">
        <div class="main-wrapper">
            <!-- Header -->
            @include('components.header')

            <!-- Sidebar -->
            @include('components.sidebar')

            <!-- Main Content -->
            <div class="main-content main-contant-01">
                @yield('content')
            </div>

            <!-- Footer -->
            @include('components.footer')
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS Libraries -->
    <script src="{{ asset('library/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('library/moment/min/moment.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <!-- Template JS Files -->
    <script src="{{ asset('js/stisla.js') }}"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/menu-search.js') }}"></script>
    <script>
        // Initialize menu search when document is ready
        $(document).ready(function() {
            initMenuSearch();
        });
    </script>

    <!-- Additional JS (if any) -->
    @stack('scripts')
    
    <!-- Test script -->
    <script>
        console.log('Layout scripts loaded');
        // Test if jQuery and SweetAlert2 are available
        if (typeof jQuery !== 'undefined') {
            console.log('jQuery version in layout:', jQuery.fn.jquery);
        } else {
            console.error('jQuery is not loaded in layout!');
        }
        if (typeof Swal !== 'undefined') {
            console.log('SweetAlert2 is loaded');
        } else {
            console.error('SweetAlert2 is not loaded!');
        }
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const activeItem = document.querySelector('.sidebar-menu .active');
        const sidebarWrapper = document.querySelector('.main-sidebar');

        if (activeItem && sidebarWrapper) {
            const itemOffsetTop = activeItem.offsetTop;
            sidebarWrapper.scrollTop = itemOffsetTop - 100;
        }
    });
</script>
</body>
</html>
