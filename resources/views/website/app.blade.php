<!DOCTYPE html>
<html class="no-js" lang="en">

    <head>
        <meta charset="utf-8">
        <title>Evara - eCommerce HTML Template</title>
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta property="og:title" content="">
        <meta property="og:type" content="">
        <meta property="og:url" content="">
        <meta property="og:image" content="">
        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/imgs/theme/favicon.svg">
        <!-- Template CSS -->
        <link rel="stylesheet" href="{{asset('assets/css/main.css')}}">
        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite('resources/js/app.jsx')
        @inertiaHead
    </head>
    <body>
        @inertia

        <!-- Preloader Start -->
        <div id="preloader-active">
            <div class="preloader d-flex align-items-center justify-content-center">
                <div class="preloader-inner position-relative">
                    <div class="text-center">
                        <h5 class="mb-10">Now Loading</h5>
                        <div class="loader">
                            <div class="bar bar1"></div>
                            <div class="bar bar2"></div>
                            <div class="bar bar3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Vendor JS-->

        <script src="{{asset('assets/js/vendor/modernizr-3.6.0.min.js')}}"></script>
        <script src="{{asset('assets/js/vendor/jquery-3.6.0.min.js')}}"></script>
        <script src="{{asset('assets/js/vendor/jquery-migrate-3.3.0.min.js')}}"></script>
        <script src="{{asset('assets/js/vendor/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/plugins/slick.js')}}"></script>
        <script src="{{asset('assets/js/plugins/jquery.syotimer.min.js')}}"></script>
        <script src="{{asset('assets/js/plugins/wow.js')}}"></script>
        <script src="{{asset('assets/js/plugins/jquery-ui.js')}}"></script>
        <script src="{{asset('assets/js/plugins/perfect-scrollbar.js')}}"></script>
        <script src="{{asset('assets/js/plugins/magnific-popup.js')}}"></script>
        <script src="{{asset('assets/js/plugins/select2.min.js')}}"></script>
        <script src="{{asset('assets/js/plugins/waypoints.js')}}"></script>
        <script src="{{asset('assets/js/plugins/counterup.js')}}"></script>
        <script src="{{asset('assets/js/plugins/jquery.countdown.min.js')}}"></script>
        <script src="{{asset('assets/js/plugins/images-loaded.js')}}"></script>
        <script src="{{asset('assets/js/plugins/isotope.js')}}"></script>
        <script src="{{asset('assets/js/plugins/scrollup.js')}}"></script>
        <script src="{{asset('assets/js/plugins/jquery.vticker-min.js')}}"></script>
        <script src="{{asset('assets/js/plugins/jquery.theia.sticky.js')}}"></script>
        <script src="{{asset('assets/js/plugins/jquery.elevatezoom.js')}}"></script>
        <script src="{{asset('assets/js/main.js')}}"></script>
        <script src="{{asset('assets/js/shop.js')}}"></script>       
    </body>
    
</html>