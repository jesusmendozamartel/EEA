<?php

if (session()->get('logged_in') === true) {
?>

<!-- begin:: Header Mobile -->
<div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
        <a href="<?= site_url('login/main') ?>">
            <img alt="Logo" width="40px" height="40px" src="<?= base_url('assets/media/logos/logo_eea.png') ?>" />
        </a>
    </div>

    <div class="kt-header-mobile__toolbar">
        <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler"><span></span></button>
        <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more-1"></i></button>
    </div>
</div>

<!-- end:: Header Mobile -->

<div class="kt-grid kt-grid--hor kt-grid--root">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid--ver kt-page" style="min-height:100vh;">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid--hor kt-wrapper" id="kt_wrapper" style="min-height: 100vh; display:flex; flex-direction:column;">

            <!-- begin:: Header -->
            <div id="kt_header" class="kt-header kt-header--fixed" data-ktheader-minimize="on">
                <div class="kt-container">

                    <!-- begin:: Brand -->
                    <div class="kt-header__brand kt-grid__item" id="kt_header_brand">
                        <a class="kt-header__brand-logo" href="<?= site_url('login/main') ?>">
                            <img alt="Logo" width="52px" height="52px" src="<?= base_url('assets/media/logos/logo_eea.png') ?>" class="kt-header__brand-logo-default" />
                            <img alt="Logo" width="12px" height="12px" src="<?= base_url('assets/media/logos/logo_eea.png') ?>" class="kt-header__brand-logo-sticky" />
                        </a>
                    </div>

                    <!-- end:: Brand -->

                    <!-- begin: Header Menu -->
                    <button class="kt-header-menu-wrapper-close" id="kt_header_menu_mobile_close_btn"><i class="la la-close"></i></button>

                    <div class="kt-header-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_header_menu_wrapper">
                        <div id="kt_header_menu" class="kt-header-menu kt-header-menu-mobile">
                            <ul class="kt-menu__nav">

                                <li class="kt-menu__item kt-menu__item--open kt-menu__item--here kt-menu__item--submenu kt-menu__item--rel" data-ktmenu-submenu-toggle="click" aria-haspopup="true">
                                    <a href="<?= site_url('login/main') ?>" class="kt-menu__link">
                                        <span class="kt-menu__link-text">Inicio</span>
                                        <i class="kt-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                </li>

                                <li class="kt-menu__item kt-menu__item--submenu kt-menu__item--rel" data-ktmenu-submenu-toggle="click" aria-haspopup="true">
                                    <a href="javascript:;" class="kt-menu__link kt-menu__toggle">
                                        <span class="kt-menu__link-text">GENERACIÓN</span>
                                        <i class="kt-menu__ver-arrow la la-angle-right"></i>
                                    </a>

                                    <div class="kt-menu__submenu kt-menu__submenu--classic kt-menu__submenu--left">
                                        <ul class="kt-menu__subnav">

                                            <li class="kt-menu__item" aria-haspopup="true">
                                                <a href="<?= site_url('genera') ?>" class="kt-menu__link">
                                                    <i class="kt-menu__link-icon flaticon-diagram"><span></span></i>
                                                    <span class="kt-menu__link-text">Sistema Intermedio</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>

                    <!-- end: Header Menu -->

                    <!-- begin:: Header Topbar -->
                    <?= view('estructura/topbar') ?>
                    <!-- end:: Header Topbar -->

                </div>
            </div>

            <!-- end:: Header -->

<?php
} else {
    return redirect()->to('/EEA/login');
}
?>