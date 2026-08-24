
<div class="kt-header__topbar kt-grid__item">

   <!--begin: User bar -->
    <div class="kt-header__topbar-item kt-header__topbar-item--user">
        <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
            <span class="kt-header__topbar-welcome">Bienvenid@,</span>
            <span class="kt-header__topbar-username"><?= session()->get('username') ?></span>
            <span class="kt-header__topbar-icon"><b><?= strtoupper(substr(session()->get('username'), 0, 1)); ?></b></span>
            <img alt="Pic" src="<?php echo base_url(); ?>assets/media/users/300_21.jpg" class="kt-hidden" />
        </div>
        <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim dropdown-menu-xl">

            <!--begin: Head -->
            <div class="kt-user-card kt-user-card--skin-dark kt-notification-item-padding-x" style="background-image: url(<?php echo base_url(); ?>assets/media/misc/bg-1.jpg)">
                <div class="kt-user-card__avatar">
                    <!--use below badge element instead the user avatar to display username's first letter(remove kt-hidden class to display it) -->
                    <span class="kt-badge--bold kt-font-success"><?= strtoupper(substr(session()->get('username'), 0, 1)); ?></span>
                </div>
                <div class="kt-user-card__name">
                   <?= session()->get('username'); ?>
                </div>

            </div>

            <!--end: Head -->

            <!--begin: Navigation -->
            <div class="kt-notification">
                <div class="kt-notification__custom kt-space-between">
                    <a href="<?php echo site_url('login/logout') ?>" target="_self" class="btn btn-label btn-label-brand btn-sm btn-bold">Cerrar Sesión</a>
                </div>
            </div>

            <!--end: Navigation -->
        </div>
    </div>

    <!--end: User bar -->
</div>
