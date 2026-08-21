<!doctype html>
<html class="no-js" lang="en">

    <!-- begin::Head -->
    <head>
        <meta charset="utf-8"/>

        <title>.:DNCN-INEI:.Sistema de Consultas de SUNAT</title>
        <meta name="description" content="Login page example"> 
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!--begin::Fonts -->
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
            WebFont.load({
                google: {"families": ["Poppins:300,400,500,600,700"]},
                active: function () {
                    sessionStorage.fonts = true;
                }
            });
        </script>
        <!--end::Fonts -->
        
        <!--begin::Page Custom Styles(used by this page) --> 
        <link href="<?php echo base_url(); ?>assets/css/pages/general/login/login-2.css" rel="stylesheet" type="text/css" />
        <!--end::Page Custom Styles -->

        <!--begin::Global Theme Styles(used by all pages) -->
        <link href="<?php echo base_url(); ?>assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
        <!--end::Global Theme Styles -->

        <!--begin::Layout Skins(used by all pages) -->
        <!--end::Layout Skins -->

        <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/media/logos/favicon.ico" />
        <style type="text/css">
            .form-control {
                color: #ffe7c3 !important;
            }  
            
        </style>
    </head>
    <!-- end::Head -->

    <!-- begin::Body -->
    <body  style="background-image: url(<?php echo base_url(); ?>assets/media/demos/header.jpg); background-position: center top; background-size: 100% 180px;"  class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-page--loading"  >
    <!-- begin::Page loader -->

        <!-- end::Page Loader -->        
        <!-- begin:: Page -->
        <div class="kt-grid kt-grid--ver kt-grid--root kt-page">
            <div class="kt-grid kt-grid--hor kt-grid--root kt-login kt-login--v2 kt-login--signin" id="kt_login">
                <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" style="background-image: url(<?php echo base_url(); ?>assets/media/bg/bg-2.jpg);">
                    <div class="kt-grid__item kt-grid__item--fluid kt-login__wrapper">
                        <div class="kt-login__container">
                            <div class="kt-login__logo">
                                <a href="#">
                                    <img src="<?php echo base_url(); ?>assets/media/logos/logo_inei.jpg">  	
                                </a>
                            </div>
                            <div class="kt-login__signin">
                                <div class="kt-login__head">
                                    <h3 class="kt-login__title">Bienvenido al Sistema de Consultas de los Sectores Institucionales</h3>
                                </div>
                                <form action="<?= site_url('login/valida') ?>" method="post" class="kt-form">
                                <div class="input-group">
                                    <input class="form-control" type="text" placeholder="Usuario" id="username" name="username" autocomplete="off" autofocus="">
                                </div>
                                <div class="input-group">
                                    <input class="form-control" type="password" placeholder="Password" id="password" name="password">
                                </div>
                                <div class="kt-login__actions">
                                    <button id="kt_login_signin_submit" class="btn btn-pill kt-login__btn-primary">Ingresar</button>
                                </div>
                                </form>
                            </div>
                        </div>	
                    </div>
                </div>
            </div>	
        </div>

        <!-- end:: Page -->
        <!-- begin::Global Config(global config for global JS sciprts) -->
        <script>
            var KTAppOptions = {"colors": {"state": {"brand": "#366cf3", "light": "#ffffff", "dark": "#282a3c", "primary": "#5867dd", "success": "#34bfa3", "info": "#36a3f7", "warning": "#ffb822", "danger": "#fd3995"}, "base": {"label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"], "shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]}}};
            var base_url = "<?php echo base_url(); ?>";
        </script>
        <!-- end::Global Config -->

        <!--begin::Global Theme Bundle(used by all pages) -->
        <script src="<?php echo base_url(); ?>assets/vendors/global/vendors.bundle.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>assets/js/scripts.bundle.js" type="text/javascript"></script>
        <!--end::Global Theme Bundle -->




        <!--begin::Page Scripts(used by this page) -->
        <script src="<?php echo base_url(); ?>assets/js/pages/login/login-general.js" type="text/javascript"></script>
        <!--end::Page Scripts -->

        <script src="<?php echo base_url(); ?>assets/vendors/general/jquery-validation/dist/localization/messages_es_PE.min.js" type="text/javascript"></script>
    </body>
</html>
