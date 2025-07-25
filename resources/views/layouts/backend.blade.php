<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SIRUANG</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{asset ('assets/vendors/simple-line-icons/css/simple-line-icons.css')}}">
    <link rel="stylesheet" href="{{asset ('assets/vendors/flag-icon-css/css/flag-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset ('assets/vendors/css/vendor.bundle.base.css')}}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{asset ('assets/vendors/font-awesome/css/font-awesome.min.css')}}" />
    <link rel="stylesheet" href="{{asset ('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset ('assets/vendors/jvectormap/jquery-jvectormap.css')}}">
    <link rel="stylesheet" href="{{asset ('assets/vendors/daterangepicker/daterangepicker.css')}}">
    <link rel="stylesheet" href="{{asset ('assets/vendors/chartist/chartist.min.css')}}">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{asset ('assets/css/vertical-light-layout/style.css')}}">
    @yield('styles')
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{asset ('assets/images/favicon.png')}}" />
  </head>
  <body>
    <div class="container-scroller">
      @include('layouts.component-backend.navbar')
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
       @include('layouts.component-backend.sidebar')
        <!-- partial -->
        @yield('content')
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{asset ('assets/vendors/js/vendor.bundle.base.js')}}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{asset ('assets/vendors/chart.js/chart.umd.js')}}"></script>
    <script src="{{asset ('assets/vendors/jvectormap/jquery-jvectormap.min.js')}}"></script>
    <script src="{{asset ('assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
    <script src="{{asset ('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{asset ('assets/vendors/moment/moment.min.js')}}"></script>
    <script src="{{asset ('assets/vendors/daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{asset ('assets/vendors/chartist/chartist.min.js')}}"></script>
    <script src="{{asset ('assets/vendors/progressbar.js/progressbar.min.js')}}"></script>
    <script src="{{asset ('assets/js/jquery.cookie.js')}}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{asset ('assets/js/off-canvas.js')}}"></script>
    <script src="{{asset ('assets/js/hoverable-collapse.js')}}"></script>
    <script src="{{asset ('assets/js/misc.js')}}"></script>
    <script src="{{asset ('assets/js/settings.js')}}"></script>
    <script src="{{asset ('assets/js/todolist.js')}}"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="{{asset ('assets/js/dashboard.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('js')
    @stack('scripts')
    @include('sweetalert::alert')
    <!-- End custom js for this page -->
  </body>
</html>