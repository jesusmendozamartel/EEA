"use strict";

// Class Definition
var KTLoginGeneral = function () {

    var login = $('#kt_login');

    var showErrorMsg = function (form, type, msg) {
        var alert = $('<div class="kt-alert kt-alert--outline alert alert-' + type + ' alert-dismissible" role="alert">\
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>\
			<span></span>\
		</div>');

        form.find('.alert').remove();
        alert.prependTo(form);
        //alert.animateClass('fadeIn animated');
        KTUtil.animateClass(alert[0], 'fadeIn animated');
        alert.find('span').html(msg);
    }

    var handleSignInFormSubmit = function () {
        $('#kt_login_signin_submit').click(function (e) {
            e.preventDefault();
            var btn = $(this);
            var form = $(this).closest('form');

            form.validate({
                rules: {
                    username: {
                        required: true
                    },
                    password: {
                        required: true
                    }
                }
            });

            if (!form.valid()) {
                return;
            }

            //btn.addClass('kt-spinner kt-spinner--right kt-spinner--sm kt-spinner--light').attr('disabled', true);

            //Ajax Load data from ajax
            var data = "username=" + $("#username").val() + "&password=" + $("#password").val();

            $.ajax({
                beforeSend: function () {
                    btn.addClass('kt-spinner kt-spinner--right kt-spinner--sm kt-spinner--light').attr('disabled', true);
                },
                type: "POST",
                cache: false,
                url: base_url + 'login/valida',
                data: data,
                success: function (response) {
                    console.log(response);
                    // $this->load->view('main/principal_view', $data);
                    
                    if (!response) {
                        setTimeout(function () {
                            showErrorMsg(form, 'danger', 'Nombre de usuario o contraseña incorrecta. Inténtalo de nuevo.');
                        }, 800);
                        $("#username").focus();
                    }else{
                        //alert('OK');
                        window.location.href = base_url + 'main';
                    }
                    
                    btn.removeClass('kt-spinner kt-spinner--right kt-spinner--sm kt-spinner--light').attr('disabled', false);
                },
                error: function (e) {
                },
                dataType: 'html'
            });
        });
    }

    // Public Functions
    return {
        // public functions
        init: function () {
            handleSignInFormSubmit();
        }
    };
}();

// Class Initialization
jQuery(document).ready(function () {
    KTLoginGeneral.init();
});