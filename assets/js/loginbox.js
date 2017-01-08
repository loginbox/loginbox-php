// Check for jQuery
if (typeof window.jQuery === 'undefined') {
    throw new Error('loginBox requires jQuery')
}

(function ($) {
    var __loginBox = {
        options: {
            cookie: {
                name: '__awt',
                duration: 30,
                domain: ''
            },
            callbacks: {
                login: null,
                register: null,
                logout: null
            },
            placeholder: null,
            preload: false
        },
        auth_token: null,
        box: null,
        setOptions: function (options) {
            // Extend object options
            this.options = $.extend(this.options, options);
        },
        init: function () {
            // loginBox listeners
            // Close dialog
            $(document).on('click', '.identity-login-box.dialog .box-header .btn-close', function () {
                // Click on menu
                $(this).trigger('dispose');
                $(this).trigger('login-box-dispose');
                $(this).closest('.identity-login-box-container').detach();
            });

            // Submit form - clear errors
            $(document).on('submit', '.identity-login-box form', function () {
                loginBox.clearErrors();
            });

            // Switch forms
            $(document).on('click', '.identity-login-box .box-footer .ft-lnk', function () {
                // Get form reference
                var fref = $(this).data('fref');

                // Hide all forms and show the selected one
                __loginBox.showBox(fref);

                // Reset all forms
                $('.identity-login-box form').each(function () {
                    loginBox.resetForm($(this));
                    loginBox.clearFormReport($(this));
                });

                // Adjust footer links
                $('.ft-lnk, .ft-lnk-bull').removeClass('hidden');
                $(this).addClass('hidden');
                $('.ft-lnk-bull.' + fref).addClass('hidden');
            });

            // Reset and recover forms
            $(document).on('click', '.identity-login-box .box-main .bx-sub-title.action.reset', function () {
                // Hide all forms and show the selected one
                __loginBox.showBox('reset');
            });

            // Reset and recover forms
            $(document).on('click', '.identity-login-box .box-main .bx-sub-title.action.recover', function () {
                // Hide all forms and show the selected one
                __loginBox.showBox('recover');
            });
        },
        passwordRecoverySuccess: function (title) {
            __loginBox.showSuccess(title, function () {
                __loginBox.showBox('rest')
            }, 2000);
        },
        passwordResetSuccess: function (title) {
            __loginBox.showSuccess(title, function () {
                __loginBox.showBox('login')
            }, 2000);
        },
        loginSuccess: function (title, redirect_url) {
            __loginBox.showSuccess(title, function () {
                if ($.type(redirect_url) != 'undefined') {
                    window.location = redirect_url;
                }
            }, 2000);
        },
        registerSuccess: function (title, redirect_url) {
            __loginBox.showSuccess(title, function () {
                if ($.type(redirect_url) != 'undefined') {
                    window.location = redirect_url;
                }
            }, 2000);
        },
        showSuccess: function (title, callback, timeout) {
            __loginBox.showBox('success');
            $('.box-main.success .bx-succ-title').html(title);
            if ($.type(callback) != 'undefined') {
                setTimeout(callback, timeout);
            }
        },
        showError: function (title) {
            $('.identity-login-box form:visible').addClass('has-error');
            $('.identity-login-box form:visible .inp-container ~ .err-container').html(title);
        },
        clearErrors: function () {
            $('.identity-login-box form').removeClass('has-error');
            $('.identity-login-box .err-container').empty();
        },
        showBox: function (box) {
            $('.box-main').addClass('hidden');
            $('.box-main.' + box).removeClass('hidden');
        },
        resetForm: function (jqForm, full) {
            // Reset form (full or password-only)
            if (full == 1 || full == undefined)
                jqForm.trigger('reset');
            else
                jqForm.find('input[type=password]').val('');
        }
    };

    // Extend
    window.loginBox = window.loginBox || {};
    window.loginBox.options = $.extend(__loginBox.options, window.loginBox.options);
    window.loginBox = $.extend(__loginBox, window.loginBox);

    // init
    window.loginBox.init();
})(jQuery);