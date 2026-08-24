<?= view('estructura/header') ?>

<!-- begin::Body -->
<body style="background-image: url(<?= base_url('assets/media/demos/header.jpg') ?>); background-position: center top; background-size: 100% 180px; min-height:100vh; display:flex; flex-direction:column;" class="kt-page--loading-enabled kt-page--loading kt-page--fluid kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-page--loading">

    <!-- begin::Page loader -->

    <!-- end::Page Loader -->

    <!-- begin:: Page -->
    <?= view('estructura/menu') ?>

    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-grid--stretch" style="flex:1;">
        <div class="kt-container kt-body kt-grid kt-grid--ver" id="kt_body">
            <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor">

                <!-- begin:: Subheader -->
                <div class="kt-subheader kt-grid__item" id="kt_subheader">
                    <div class="kt-subheader__main">
                        <h3 class="kt-subheader__title">Inicio</h3>

                        <div class="kt-subheader__breadcrumbs">
                            <a href="#" class="kt-subheader__breadcrumbs-home">
                                <i class="flaticon2-shelter"></i>
                            </a>

                            <span class="kt-subheader__breadcrumbs-separator"></span>

                            <a href="" class="kt-subheader__breadcrumbs-link">
                                Inicio
                            </a>

                            <span class="kt-subheader__breadcrumbs-separator"></span>

                            <a href="" class="kt-subheader__breadcrumbs-link">
                                Información
                            </a>
                        </div>
                    </div>
                </div>

                <!-- end:: Subheader -->

                <!-- begin:: Content -->
                <div class="kt-content kt-grid__item">

                    <!-- Begin::Dashboard 2 -->
                    <div class="kt-container kt-grid__item kt-grid__item--fluid">

                        <div class="kt-portlet w-75 mx-auto small">
                            <div class="kt-portlet__body">

                                <div class="kt-infobox">

                                    <div class="kt-infobox__header">
                                        <h2 class="kt-infobox__title">Información</h2>
                                    </div>

                                    <div class="kt-infobox__body">

                                        <div class="kt-infobox__section">
                                            <div class="kt-infobox__content">

                                            </div>
                                        </div>

                                        <div class="kt-infobox__section">

                                            <h5 class="kt-infobox__subtitle text-center">
                                                SISTEMA DE CONSULTAS DE GENERACIÓN DE LOS SECTORES INSTITUCIONALES
                                            </h5>

                                            <h5 class="kt-infobox__subtitle text-center"></h5>

                                            <div class="kt-infobox__section">
                                                <div class="kt-infobox__content">

                                                    <div class="table-responsive">

                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!-- End::Dashboard 2 -->

                </div>

                <!-- end:: Content -->

            </div>
        </div>
    </div>

    <?= view('estructura/footer') ?>

</body>
</html>