// Check for jQuery
if (typeof window.jQuery === 'undefined') {
    throw new Error('loginBox requires jQuery')
}

(function ($) {
    $(document).one("ready", function () {
        // Remember me options
        $(document).on("click", ".loginBox .ricnt label", function () {
            // Set selected
            $(".loginBox .rocnt").removeClass("selected");
            $(this).closest(".rocnt").addClass("selected");
            var id = $(this).closest(".rocnt").attr("id");

            // Set notes selected
            $(".rnotes .nt").removeClass("selected");
            $(".nt." + id).addClass("selected");
        });

        $(document).on("mouseenter", ".loginBox .ricnt label", function () {
            // Get id
            var id = $(this).closest(".rocnt").attr("id");

            // Set notes selected
            $(".rnotes .nt").removeClass("selected");
            $(".nt." + id).addClass("selected");
        });

        $(document).on("mouseleave", ".loginBox .ricnt label", function () {
            // Reset selected
            var rcnts = $(".loginBox .rocnt.selected");
            var id = rcnts.attr("id");

            // Set notes selected
            $(".rnotes .nt").removeClass("selected");
            $(".nt." + id).addClass("selected");
        });

        // Close popup
        $(document).on("click", ".loginBox .header .close", function () {
            $(this).trigger("dispose");
        });
    });
})(jQuery);