<!doctype html>
<html class="fixed">

<head>

    <!-- Basic -->
    <meta charset="UTF-8">

    <title>BPAD | Monitoring Persediaan</title>

    <meta name="keywords" content="HTML5 Admin Template" />
    <meta name="description" content="Monitoring Admin - Persediaan Aset">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <!-- Web Fonts  -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light"
        rel="stylesheet" type="text/css">

    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="57x57" href="favicon-bpad/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="favicon-bpad/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="favicon-bpad/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="favicon-bpad/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="favicon-bpad/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="favicon-bpad/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="favicon-bpad/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="favicon-bpad/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon-bpad/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="favicon-bpad/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-bpad/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicon-bpad/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-bpad/favicon-16x16.png">
    <link rel="manifest" href="favicon-bpad/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="favicon-bpad/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="vendor/animate/animate.compat.css">
    <link rel="stylesheet" href="vendor/font-awesome/css/all.min.css" />
    <link rel="stylesheet" href="vendor/boxicons/css/boxicons.min.css" />
    <link rel="stylesheet" href="vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css" />
    <link rel="stylesheet" href="vendor/select2/css/select2.css" />
    <link rel="stylesheet" href="vendor/select2-bootstrap-theme/select2-bootstrap.min.css" />
    <link rel="stylesheet" href="vendor/datatables/media/css/dataTables.bootstrap5.css" />
    <link rel="stylesheet" href="vendor/morris/morris.css" />
	<link rel="stylesheet" href="vendor/chartist/chartist.min.css" />
    
    <!-- Theme CSS -->
    <link rel="stylesheet" href="css/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="css/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">

    <!-- Head Libs -->
    <script src="vendor/modernizr/modernizr.js"></script>

</head>

<body>
    <section class="body">
        {{-- Navbar --}}
        @include('Backend.Template.navbar')

        {{-- Content --}}
        @yield('content')


    </section>

</body>

<!-- Vendor -->
<script src="vendor/jquery/jquery.js"></script>
<script src="vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
<script src="vendor/popper/umd/popper.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script src="vendor/common/common.js"></script>
<script src="vendor/nanoscroller/nanoscroller.js"></script>
<script src="vendor/magnific-popup/jquery.magnific-popup.js"></script>
<script src="vendor/jquery-placeholder/jquery.placeholder.js"></script>

<!-- Specific Page Vendor -->
<script src="vendor/select2/js/select2.js"></script>
<script src="vendor/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/media/js/dataTables.bootstrap5.min.js"></script>
<script src="vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js"></script>
<script src="vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js"></script>
<script src="vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js"></script>
<script src="vendor/datatables/extras/TableTools/JSZip-2.5.0/jszip.min.js"></script>
<script src="vendor/datatables/extras/TableTools/pdfmake-0.1.32/pdfmake.min.js"></script>
<script src="vendor/datatables/extras/TableTools/pdfmake-0.1.32/vfs_fonts.js"></script>

<!-- Chart Vendor -->
<script src="vendor/jquery-appear/jquery.appear.js"></script>
<script src="vendor/jquery.easy-pie-chart/jquery.easypiechart.js"></script>
<script src="vendor/flot/jquery.flot.js"></script>
<script src="vendor/flot.tooltip/jquery.flot.tooltip.js"></script>
<script src="vendor/flot/jquery.flot.pie.js"></script>
<script src="vendor/flot/jquery.flot.categories.js"></script>
<script src="vendor/flot/jquery.flot.resize.js"></script>
<script src="vendor/jquery-sparkline/jquery.sparkline.js"></script>
<script src="vendor/raphael/raphael.js"></script>
<script src="vendor/morris/morris.js"></script>
<script src="vendor/gauge/gauge.js"></script>
<script src="vendor/snap.svg/snap.svg.js"></script>
<script src="vendor/liquid-meter/liquid.meter.js"></script>
<script src="vendor/chartist/chartist.js"></script>

<!-- Theme Base, Components and Settings -->
<script src="js/theme.js"></script>

<!-- Theme Custom -->
<script src="js/custom.js"></script>

<!-- Theme Initialization Files -->
<script src="js/theme.init.js"></script>

<!-- Examples -->
<script src="js/examples/examples.datatables.default.js"></script>
<script src="js/examples/examples.datatables.row.with.details.js"></script>
<script src="js/examples/examples.datatables.tabletools.js"></script>
<!-- Examples -->
<style>
    #ChartistCSSAnimation .ct-series.ct-series-a .ct-line {
        fill: none;
        stroke-width: 4px;
        stroke-dasharray: 5px;
        -webkit-animation: dashoffset 1s linear infinite;
        -moz-animation: dashoffset 1s linear infinite;
        animation: dashoffset 1s linear infinite;
    }

    #ChartistCSSAnimation .ct-series.ct-series-b .ct-point {
        -webkit-animation: bouncing-stroke 0.5s ease infinite;
        -moz-animation: bouncing-stroke 0.5s ease infinite;
        animation: bouncing-stroke 0.5s ease infinite;
    }

    #ChartistCSSAnimation .ct-series.ct-series-b .ct-line {
        fill: none;
        stroke-width: 3px;
    }

    #ChartistCSSAnimation .ct-series.ct-series-c .ct-point {
        -webkit-animation: exploding-stroke 1s ease-out infinite;
        -moz-animation: exploding-stroke 1s ease-out infinite;
        animation: exploding-stroke 1s ease-out infinite;
    }

    #ChartistCSSAnimation .ct-series.ct-series-c .ct-line {
        fill: none;
        stroke-width: 2px;
        stroke-dasharray: 40px 3px;
    }

    @-webkit-keyframes dashoffset {
        0% {
            stroke-dashoffset: 0px;
        }

        100% {
            stroke-dashoffset: -20px;
        };
    }

    @-moz-keyframes dashoffset {
        0% {
            stroke-dashoffset: 0px;
        }

        100% {
            stroke-dashoffset: -20px;
        };
    }

    @keyframes dashoffset {
        0% {
            stroke-dashoffset: 0px;
        }

        100% {
            stroke-dashoffset: -20px;
        };
    }

    @-webkit-keyframes bouncing-stroke {
        0% {
            stroke-width: 5px;
        }

        50% {
            stroke-width: 10px;
        }

        100% {
            stroke-width: 5px;
        };
    }

    @-moz-keyframes bouncing-stroke {
        0% {
            stroke-width: 5px;
        }

        50% {
            stroke-width: 10px;
        }

        100% {
            stroke-width: 5px;
        };
    }

    @keyframes bouncing-stroke {
        0% {
            stroke-width: 5px;
        }

        50% {
            stroke-width: 10px;
        }

        100% {
            stroke-width: 5px;
        };
    }

    @-webkit-keyframes exploding-stroke {
        0% {
            stroke-width: 2px;
            opacity: 1;
        }

        100% {
            stroke-width: 20px;
            opacity: 0;
        };
    }

    @-moz-keyframes exploding-stroke {
        0% {
            stroke-width: 2px;
            opacity: 1;
        }

        100% {
            stroke-width: 20px;
            opacity: 0;
        };
    }

    @keyframes exploding-stroke {
        0% {
            stroke-width: 2px;
            opacity: 1;
        }

        100% {
            stroke-width: 20px;
            opacity: 0;
        };
    }
</style>
<script src="js/examples/examples.charts.js"></script>

</html>
