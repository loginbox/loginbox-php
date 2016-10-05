// Check for jQuery
if (typeof window.jQuery === 'undefined') {
    throw new Error('loginBox requires jQuery')
}

(function ($) {
    var __loginBox = {
        options: {
            cookie: {
                name: "__awt",
                duration: 30,
                domain: ""
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
            $(document).on("click", ".identity-login-box.dialog .box-header .btn-close", function () {
                // Click on menu
                $(this).trigger("dispose");
                $(this).trigger("login-box-dispose");
                $(this).closest(".identity-login-box-container").detach();
            });

            // Switch forms
            $(document).on("click", ".identity-login-box .box-footer .ft-lnk", function () {
                // Get form reference
                var fref = $(this).data("fref");

                // Hide all forms and show the selected one
                $(".box-main").addClass("hidden");
                $(".box-main." + fref).removeClass("hidden");

                // Reset all forms
                $(".identity-login-box form").each(function () {
                    loginBox.resetForm($(this));
                    loginBox.clearFormReport($(this));
                });

                // Adjust footer links
                $(".ft-lnk, .ft-lnk-bull").removeClass("hidden");
                $(this).addClass("hidden");
                $(".ft-lnk-bull." + fref).addClass("hidden");
            });

            // Reset and recover forms
            $(document).on("click", ".identity-login-box .box-main .bx-sub-title.action.reset", function () {
                // Hide all forms and show the selected one
                $(".box-main").addClass("hidden");
                $(".box-main.reset").removeClass("hidden");
            });

            // Reset and recover forms
            $(document).on("click", ".identity-login-box .box-main .bx-sub-title.action.recover", function () {
                // Hide all forms and show the selected one
                $(".box-main").addClass("hidden");
                $(".box-main.recover").removeClass("hidden");
            });

            // Login form
            $(document).on('submit', '.identity-login-box .box-main.login form[data-async]', function (ev) {
                // Stops the Default Action (if any)
                ev.preventDefault();

                // Submit form
                loginBox.submitForm(ev, $(this), function (ev, response) {
                    // Check if there is an error
                    if (typeof response.body['error'] != "undefined") {
                        // Show message
                        loginBox.setFormErrorReport($(this), response.body.error.payload.message);
                    } else if (typeof response.body['login'] != "undefined") {
                        // Get status and set report message
                        var status = response.body.login.payload.status;
                        if (status == 1 && response.body.login.payload.auth_token != undefined) {
                            // Set auth token
                            loginBox.setAuthToken(response.body.login.payload.auth_token);

                            // Show success
                            $(".box-main").addClass("hidden");
                            $(".box-main.success").removeClass("hidden");
                            $(".box-main.success .bx-succ-title").html(response.body.login.payload.message);

                            // Login callback
                            if (typeof loginBox.options.login_callback == 'function') {
                                setTimeout(function () {
                                    loginBox.options.login_callback.call();
                                }, 2000);
                            }
                        } else
                            loginBox.setFormErrorReport($(this), response.body.login.payload.message);
                    }

                    // Reset form
                    loginBox.resetForm($(this));
                });
            });

            // Register form
            $(document).on('submit', '.identity-login-box .box-main.register form[data-async]', function (ev) {
                // Stops the Default Action (if any)
                ev.preventDefault();

                // Submit form
                loginBox.submitForm(ev, $(this), function (ev, response) {
                    // Check if there is an error
                    if (typeof response.body['error'] != "undefined") {
                        // Show message
                        loginBox.setFormErrorReport($(this), response.body.error.payload.message);
                    } else if (typeof response.body['register'] != "undefined") {
                        // Get status and set report message
                        var status = response.body.register.payload.status;
                        if (status == 1) {
                            // Check and set auth_token
                            if (response.body.register.payload.auth_token != undefined) {
                                // Set auth token
                                loginBox.setAuthToken(response.body.register.payload.auth_token);
                            }

                            // Show success
                            $(".box-main").addClass("hidden");
                            $(".box-main.success").removeClass("hidden");
                            $(".box-main.success .bx-succ-title").html(response.body.register.payload.message);

                            // Register callback
                            if (typeof loginBox.options.register_callback == 'function') {
                                setTimeout(function () {
                                    loginBox.options.register_callback.call();
                                }, 2000);
                            }
                        } else
                            loginBox.setFormErrorReport($(this), response.body.register.payload.message);

                        // Reset form
                        loginBox.resetForm($(this));
                    }
                });
            });

            // Recovery form
            $(document).on('submit', '.identity-login-box .box-main.recover form[data-async]', function (ev) {
                // Stops the Default Action (if any)
                ev.preventDefault();

                // Submit form
                loginBox.submitForm(ev, $(this), function (ev, response) {
                    // Check if there is an error
                    if (typeof response.body['error'] != "undefined") {
                        // Show message
                        loginBox.setFormErrorReport($(this), response.body.error.payload.message);
                    } else if (typeof response.body['recover'] != "undefined") {
                        // Get status and set report message
                        var status = response.body.recover.payload.status;
                        if (status == 1) {
                            // Report, set cookie and callback
                            loginBox.setFormReport($(this), response.body.recover.payload.message);

                            // Show success
                            $(".box-main").addClass("hidden");
                            $(".box-main.success").removeClass("hidden");
                            $(".box-main.success .bx-succ-title").html(response.body.recover.payload.message);

                            // Hide all forms and show the selected one
                            setTimeout(function () {
                                $(".box-main").addClass("hidden");
                                $(".box-main.reset").removeClass("hidden");
                            }, 2500);
                        } else
                            loginBox.setFormErrorReport($(this), response.body.recover.payload.message);
                    }

                    // Reset form
                    loginBox.resetForm($(this));
                });
            });

            // Reset form
            $(document).on('submit', '.identity-login-box .box-main.reset form[data-async]', function (ev) {
                // Stops the Default Action (if any)
                ev.preventDefault();

                // Submit form
                loginBox.submitForm(ev, $(this), function (ev, response) {
                    // Check if there is an error
                    if (typeof response.body['error'] != "undefined") {
                        // Show message
                        loginBox.setFormErrorReport($(this), response.body.error.payload.message);
                    } else if (typeof response.body['update'] != "undefined") {
                        // Get status and set report message
                        var status = response.body.update.payload.status;
                        if (status == 1) {
                            // Report, set cookie and callback
                            loginBox.setFormReport($(this), response.body.update.payload.message);

                            // Show success
                            $(".box-main").addClass("hidden");
                            $(".box-main.success").removeClass("hidden");
                            $(".box-main.success .bx-succ-title").html(response.body.update.payload.message);

                            // Hide all forms and show the selected one
                            setTimeout(function () {
                                $(".box-footer .ft-lnk.login").trigger("click");
                            }, 2500);
                        } else
                            loginBox.setFormErrorReport($(this), response.body.update.payload.message);
                    }

                    // Reset form
                    loginBox.resetForm($(this));
                });
            });
        },
        load: function (response) {
            // Get login dialog
            this.box = response.body[0].payload.content;

            // Load resources
            for (var key in response.head['bt_rsrc']) {
                // Get resource info
                var resource = response.head['bt_rsrc'][key];

                // Get loginBox resource
                if (resource.attributes.package == "box/loginBox") {
                    loginBox.loadCSS(resource.css);
                    loginBox.loadJS(resource.js);
                }
            }

            // Dispose loginbox
            jq(document).on("login-box-dispose", function () {
                loginBox.disposePopup();
            });

            // Trigger login box arrived
            jq(document).trigger("loginbox.ready");

            // Check for placeholder
            if (this.options.placeholder != null && this.options.preload)
                loginBox.load(this.options.placeholder);
        },
        show: function (placeholder, mode) {
            // Check if placeholder is empty
            if (typeof placeholder == 'undefined' || placeholder == null) {
                // Show popup
                var lgClone = jq(this.box).clone();
                loginBox.showPopup(lgClone);
                if (typeof mode != 'undefined' && mode != null)
                    lgClone.find(".ft-lnk." + mode).trigger("click");
            } else {
                // Set options
                this.options.placeholder = placeholder;
                this.options.preload = true;

                // Append to placeholder
                if (this.box != null)
                    jq(this.options.placeholder).append(jq(this.box).clone().find(".identity-login-box").removeClass("dialog").end());
            }
        },
        loadJS: function (href, callback) {
            $.getScript(href, function (ev) {
                // run successCallback function, if any
                if (typeof callback == 'function') {
                    callback.call();
                }
            });
        },
        loadCSS: function (href) {
            return jq("<link rel='stylesheet' href='" + href + "'>").appendTo(jq("head"));
        },
        showPopup: function (popupContent) {
            // Check and remove any previous overlays
            jq(".login-popup-overlay").detach();

            // Create popup overlay
            var popupOverlay = jq("<div />").addClass("login-popup-overlay");

            // Get left margin
            var marginLeft = "";

            // Add popup content
            var uiPopup = jq("<div />").addClass("login-popup").append(jq(popupContent).addClass("popup-content"));

            // Append
            popupOverlay.append(uiPopup).appendTo(jq(document.body));

            // Adjust position
            var contentWidth = jq(uiPopup).outerWidth();
            uiPopup.css("margin-left", -contentWidth / 2 + "px");
            // Re-adjust position (for mobile)
            setTimeout(function () {
                var contentWidth = jq(uiPopup).outerWidth();
                uiPopup.css("margin-left", -contentWidth / 2 + "px");
                uiPopup.css("left", "50%");
            }, 10);
        },
        disposePopup: function () {
            jq(".login-popup-overlay").detach();
        },
        submitForm: function (ev, jqForm, successCallback) {
            // Check if form is already posting
            if (jqForm.data("posting") == true)
                return false;

            // Initialize posting
            jqForm.data("posting", true);

            // Clear form report
            jqForm.find(".formReport").empty();

            // Form Parameters
            var formData = "";
            if (jqForm.attr('enctype') == "multipart/form-data") {
                // Initialize form data
                formData = new FormData();

                // Get form data
                var fdArray = jqForm.serializeArray();
                for (index in fdArray)
                    formData.append(fdArray[index].name, fdArray[index].value);

                // Get files (if any)
                jqForm.find("input[type='file']").each(function () {
                    if ($.type(this.files[0]) != "undefined")
                        formData.append(jq(this).attr("name"), this.files[0]);
                });
            }
            else
                formData = jqForm.serialize();

            // Disable all inputs
            jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button").prop("disabled", true).addClass("disabled");

            // Set Complete callback Handler function
            var completeCallback = function (ev) {
                // Enable inputs again
                jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button").prop("disabled", false).removeClass("disabled");

                // Set posting status false
                jqForm.data("posting", false);
            };

            // Create extra options
            var options = {
                completeCallback: completeCallback,
                withCredentials: true
            };

            // Start HTMLServerReport request
            var formAction = jqForm.attr("action");
            JSPreloader.request(formAction, "POST", formData, jqForm, function (response) {
                // Execute custom callback (if any)
                if (typeof successCallback == 'function') {
                    successCallback.call(this, ev, response);
                }
            }, options);
        },
        setFormReport: function (jqForm, report) {
            var ntf = jq("<div />").addClass("ntf").html(report);
            jqForm.find(".formReport").append(ntf);
        },
        setFormErrorReport: function (jqForm, report) {
            var ntf = jq("<div />").addClass("ntf").addClass("error").html(report);
            jqForm.find(".formReport").append(ntf);
        },
        clearFormReport: function (jqForm) {
            jqForm.find(".formReport").empty();
        },
        resetForm: function (jqForm, full) {
            // Reset form (full or password-only)
            if (full == 1 || full == undefined)
                jqForm.trigger('reset');
            else
                jqForm.find("input[type=password]").val("");
        },
        getAuthToken: function () {
            return this.auth_token;
        },
        setAuthToken: function (auth_token) {
            // Set token local
            this.auth_token = auth_token;

            // Set cookies according to settings
            lgbx_cookies.set(this.options.cookie_auth_token, this.auth_token, this.options.cookie_duration, "/", this.options.cookie_domain);
        },
        logout: function (callback) {
            // Set cookies according to settings
            lgbx_cookies.set(this.options.cookie_auth_token, null, -1);

            // Fallback callback
            if (typeof loginBox.options.logout_callback == 'function') {
                setTimeout(function () {
                    loginBox.options.logout_callback.call();
                }, 2000);
            }
        }
    };

    // Extend
    window.loginBox = window.loginBox || {};
    window.loginBox.options = $.extend(__loginBox.options, window.loginBox.options);
    window.loginBox = $.extend(__loginBox, window.loginBox);

    // init
    window.loginBox.init();
})(jQuery);