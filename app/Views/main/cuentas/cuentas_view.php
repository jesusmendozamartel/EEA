<?= view('estructura/header') ?>

<body style="background-image: url(<?= base_url('assets/media/demos/header.png') ?>); background-position: center top; background-size: 100% 180px;" class="kt-page--loading-enabled kt-page--loading kt-page--fluid kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-page--loading">

    <?= view('estructura/menu') ?>

    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-grid--stretch" style="flex:1;">

        <div class="kt-container kt-body kt-grid kt-grid--ver kt-container--fluid" id="kt_body">

            <?= view('main/common/' . $template, $data) ?>

        </div>

    </div>

    <?= view('estructura/footer') ?>

    <?php foreach ($js as $scripts) { ?>
        <script src="<?= base_url('assets/js/' . $scripts) ?>" type="text/javascript"></script>
    <?php } ?>

</body>
</html>