<!-- begin:: Footer -->
<div class="kt-footer kt-grid__item" id="kt_footer">
    <div class="kt-container">
        <div class="kt-footer__wrapper">
            <div class="kt-footer__copyright">
                <?= date('Y') ?>&nbsp;&copy;&nbsp;
                Sistema de Cuentas Nacionales
            </div>
        </div>
    </div>
</div>
<!-- end:: Footer -->


<!-- begin:: Global Config -->
<script>
    var KTAppOptions = {
        "colors": {
            "state": {
                "brand": "#366cf3",
                "light": "#ffffff",
                "dark": "#282a3c",
                "primary": "#5867dd",
                "success": "#34bfa3",
                "info": "#36a3f7",
                "warning": "#ffb822",
                "danger": "#fd3995"
            },
            "base": {
                "label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"],
                "shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]
            }
        }
    };

    var base_url = "<?= base_url() ?>";
</script>
<!-- end:: Global Config -->


<!-- begin:: Global Mandatory Vendors -->
<script src="<?= base_url('assets/vendors/general/jquery/dist/jquery.min.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/vendors/general/popper.js/dist/umd/popper.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/vendors/general/bootstrap/dist/js/bootstrap.min.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/vendors/general/js-cookie/src/js.cookie.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/vendors/general/moment/min/moment.min.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/vendors/general/sticky-js/dist/sticky.min.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/vendors/general/tooltip.js/dist/umd/tooltip.min.js') ?>" type="text/javascript"></script>
<!-- end:: Global Mandatory Vendors -->


<!-- begin:: Global Theme Bundle -->
<script src="<?= base_url('assets/js/scripts.bundle.js') ?>" type="text/javascript"></script>
<!-- end:: Global Theme Bundle -->


<!-- begin:: Validation -->
<script src="<?= base_url('assets/vendors/general/jquery-validation/dist/jquery.validate.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/vendors/general/jquery-validation/dist/additional-methods.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/vendors/general/jquery-validation/dist/localization/messages_es_PE.min.js') ?>" type="text/javascript"></script>
<!-- end:: Validation -->


<!-- begin:: SweetAlert -->
<script src="<?= base_url('assets/vendors/general/sweetalert2/dist/sweetalert2.min.js') ?>" type="text/javascript"></script>
<!-- end:: SweetAlert -->


<!-- begin:: Funciones propias -->
<script src="<?= base_url('assets/js/pages/my-script.js') ?>" type="text/javascript"></script>
<!-- end:: Funciones propias -->


<!-- begin:: JS específico de la página -->
<?php if (isset($js) && is_array($js)): ?>
    <?php foreach ($js as $archivo): ?>
        <script src="<?= base_url('assets/js/' . $archivo) ?>" type="text/javascript"></script>
    <?php endforeach; ?>
<?php endif; ?>
<!-- end:: JS específico de la página -->


</body>
</html>