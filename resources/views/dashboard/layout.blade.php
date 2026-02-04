<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sash – Bootstrap 5  Admin & Dashboard Template">
    <meta name="author" content="Spruko Technologies Private Limited">
    <meta name="keywords"
        content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/brand/favicon.ico">

    <!-- TITLE -->
    <title>Sash – Bootstrap 5 Admin & Dashboard Template </title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- STYLE CSS -->
     <link href="/assets/css/style.css" rel="stylesheet">

	<!-- Plugins CSS -->
    <link href="/assets/css/plugins.css" rel="stylesheet">

    <!--- FONT-ICONS CSS -->
    <link href="/assets/css/icons.css" rel="stylesheet">

    <!-- INTERNAL Switcher css -->
    <link href="/assets/switcher/css/switcher.css" rel="stylesheet">
    <link href="/assets/switcher/demo.css" rel="stylesheet">

</head>

<body class="app sidebar-mini ltr light-mode">



    <!-- PAGE -->
    <div class="page">
        <div class="page-main">

            <!-- app-Header -->
            <div class="app-header header sticky">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript:void(0)"></a>
                        <!-- sidebar-toggle-->
                        <a class="logo-horizontal " href="index.html">
                            <img src="/assets/images/brand/logo-white.png" class="header-brand-img desktop-logo" alt="logo">
                            <img src="/assets/images/brand/logo-dark.png" class="header-brand-img light-logo1"
                                alt="logo">
                        </a>
                        <!-- LOGO -->

                        <div class="d-flex order-lg-2 ms-auto header-right-icons">


                        </div>
                    </div>
                </div>
            </div>
            <!-- /app-Header -->

            <!--APP-SIDEBAR-->
            <div class="sticky">
                <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
                <div class="app-sidebar">
                    <div class="side-header">
                        <a class="header-brand1" href="index.html">
                            <img src="/assets/images/brand/logo-white.png" class="header-brand-img desktop-logo" alt="logo">
                            <img src="/assets/images/brand/icon-white.png" class="header-brand-img toggle-logo"
                                alt="logo">
                            <img src="/assets/images/brand/icon-dark.png" class="header-brand-img light-logo" alt="logo">
                            <img src="/assets/images/brand/logo-dark.png" class="header-brand-img light-logo1"
                                alt="logo">
                        </a>
                        <!-- LOGO -->
                    </div>
                    <div class="main-sidemenu">
                        <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg"
                                fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                            </svg></div>
                        <ul class="side-menu">

                            <li class="slide">
                                <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard.index') }}"><i
                                        class="side-menu__icon fe fe-home"></i><span
                                        class="side-menu__label">Dashboard</span></a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard.products') }}"><i
                                        class="side-menu__icon fe fe-package"></i><span
                                        class="side-menu__label">Products</span></a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard.product_items') }}"><i
                                        class="side-menu__icon fe fe-box"></i><span
                                        class="side-menu__label">Product Items</span></a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard.categories') }}"><i
                                        class="side-menu__icon fe fe-grid"></i><span
                                        class="side-menu__label">Categories</span></a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard.orders') }}"><i
                                        class="side-menu__icon fe fe-shopping-cart"></i><span
                                        class="side-menu__label">Orders</span></a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard.order_items') }}"><i
                                        class="side-menu__icon fe fe-shopping-bag"></i><span
                                        class="side-menu__label">Order Items</span></a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard.backloads') }}"><i
                                        class="side-menu__icon fe fe-truck"></i><span
                                        class="side-menu__label">Back Loads</span></a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard.companies') }}"><i
                                        class="side-menu__icon fe fe-users"></i><span
                                        class="side-menu__label">Companies</span></a>
                            </li>

                        </ul>
                        <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                                width="24" height="24" viewBox="0 0 24 24">
                                <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                            </svg></div>
                    </div>
                </div>
            </div>
            <!--/APP-SIDEBAR-->

            <!--app-content open-->
            @yield('content')
            <!--app-content close-->

        </div>


        <!-- FOOTER -->
        <footer class="footer">
            <div class="container">
                <div class="row align-items-center flex-row-reverse">
                    <div class="col-md-12 col-sm-12 text-center">
                        Copyright © <span id="year"></span> <a href="javascript:void(0)">Sash</a>. Designed with <span
                            class="fa fa-heart text-danger"></span> by <a href="javascript:void(0)"> Spruko </a> All rights reserved.
                    </div>
                </div>
            </div>
        </footer>
        <!-- FOOTER END -->

    <!-- BACK-TO-TOP -->
    <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

    <!-- JQUERY JS -->
    <script src="/assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="/assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- SPARKLINE JS-->
    <script src="/assets/js/jquery.sparkline.min.js"></script>

    <!-- Sticky js -->
    <script src="/assets/js/sticky.js"></script>

    <!-- CHART-CIRCLE JS-->
    <script src="/assets/js/circle-progress.min.js"></script>

    <!-- PIETY CHART JS-->
    <script src="/assets/plugins/peitychart/jquery.peity.min.js"></script>
    <script src="/assets/plugins/peitychart/peitychart.init.js"></script>

    <!-- SIDEBAR JS -->
    <script src="/assets/plugins/sidebar/sidebar.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="/assets/plugins/p-scroll/perfect-scrollbar.js"></script>
    <script src="/assets/plugins/p-scroll/pscroll.js"></script>
    <script src="/assets/plugins/p-scroll/pscroll-1.js"></script>

    <!-- INTERNAL CHARTJS CHART JS-->
    <script src="/assets/plugins/chart/Chart.bundle.js"></script>
    <script src="/assets/plugins/chart/utils.js"></script>

    <!-- INTERNAL SELECT2 JS -->
    <script src="/assets/plugins/select2/select2.full.min.js"></script>

    <!-- INTERNAL Data tables js-->
    <script src="/assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
    <script src="/assets/plugins/datatable/dataTables.responsive.min.js"></script>

    <!-- INTERNAL APEXCHART JS -->
    <script src="/assets/js/apexcharts.js"></script>
    <script src="/assets/plugins/apexchart/irregular-data-series.js"></script>

    <!-- INTERNAL Flot JS -->
    <script src="/assets/plugins/flot/jquery.flot.js"></script>
    <script src="/assets/plugins/flot/jquery.flot.fillbetween.js"></script>
    <script src="/assets/plugins/flot/chart.flot.sampledata.js"></script>
    <script src="/assets/plugins/flot/dashboard.sampledata.js"></script>

    <!-- INTERNAL Vector js -->
    <script src="/assets/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="/assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>

    <!-- SIDE-MENU JS-->
    <script src="/assets/plugins/sidemenu/sidemenu.js"></script>

	<!-- TypeHead js -->
	<script src="/assets/plugins/bootstrap5-typehead/autocomplete.js"></script>
    <script src="/assets/js/typehead.js"></script>

    <!-- INTERNAL INDEX JS -->
    <script src="/assets/js/index1.js"></script>

    <!-- Color Theme js -->
    <script src="/assets/js/themeColors.js"></script>

    <!-- CUSTOM JS -->
    <script src="/assets/js/custom.js"></script>

    <!-- Custom-switcher -->
    <script src="/assets/js/custom-swicher.js"></script>

    <!-- Switcher js -->
    <script src="/assets/switcher/js/switcher.js"></script>
    <!-- DATA TABLE JS-->
    <script src="/assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
    <script src="/assets/plugins/datatable/js/dataTables.buttons.min.js"></script>
    <script src="/assets/plugins/datatable/js/buttons.bootstrap5.min.js"></script>
    <script src="/assets/plugins/datatable/js/jszip.min.js"></script>
    <script src="/assets/plugins/datatable/pdfmake/pdfmake.min.js"></script>
    <script src="/assets/plugins/datatable/pdfmake/vfs_fonts.js"></script>
    <script src="/assets/plugins/datatable/js/buttons.html5.min.js"></script>
    <script src="/assets/plugins/datatable/js/buttons.print.min.js"></script>
    <script src="/assets/plugins/datatable/js/buttons.colVis.min.js"></script>
    <script src="/assets/plugins/datatable/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatable/responsive.bootstrap5.min.js"></script>
    <script src="/assets/js/table-data.js"></script>

</body>

</html>
