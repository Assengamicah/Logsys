var searchVisible = 0;
var transparent = true;

var fixedTop = false;

var navbar_initialized = false;

$(document).ready(function () {
    window_width = $(window).width();


    // Init navigation toggle for small screens
    if (window_width <= 991) {
        lbd.initRightMenu();
    }

    //  Activate the tooltips
    $('[rel="tooltip"]').tooltip();


    $('.form-control').on("focus", function () {
        $(this).parent('.input-group').addClass("input-group-focus");
    }).on("blur", function () {
        $(this).parent(".input-group").removeClass("input-group-focus");
    });

});

// activate collapse right menu when the windows is resized
$(window).resize(function () {
    if ($(window).width() <= 991) {
        lbd.initRightMenu();
    }
});

lbd = {
    misc: {
        navbar_menu_visible: 0
    },

    initRightMenu: function () {
        if (!navbar_initialized) {
            $navbar = $('nav').find('.navbar-collapse').first().clone(true);

            $sidebar = $('.sidebar');
            sidebar_color = $sidebar.data('color');

            $logo = $sidebar.find('.logo').first();
            logo_content = $logo[0].outerHTML;

            ul_content = '';

            $navbar.attr('data-color', sidebar_color);

            // add the content from the sidebar to the right menu
            content_buff = $sidebar.find('.nav').html();
            ul_content = ul_content + content_buff;

            //add the content from the regular header to the right menu
            $navbar.children('ul').each(function () {
                content_buff = $(this).html();
                ul_content = ul_content + content_buff;
            });

            ul_content = '<ul class="nav navbar-nav">' + ul_content + '</ul>';

            navbar_content = logo_content + ul_content;

            $navbar.html(navbar_content);

            $('body').append($navbar);

            background_image = $sidebar.data('image');
            if (background_image != undefined) {
                $navbar.css('background', "url('" + background_image + "')")
                    .removeAttr('data-nav-image')
                    .addClass('has-image');
            }


            $toggle = $('.navbar-toggle');

            $navbar.find('a').removeClass('btn btn-round btn-default');
            $navbar.find('button').removeClass('btn-round btn-fill btn-info btn-primary btn-success btn-danger btn-warning btn-neutral');
            $navbar.find('button').addClass('btn-simple btn-block');

            $toggle.click(function () {
                if (lbd.misc.navbar_menu_visible == 1) {
                    $('html').removeClass('nav-open');
                    lbd.misc.navbar_menu_visible = 0;
                    $('#bodyClick').remove();
                    setTimeout(function () {
                        $toggle.removeClass('toggled');
                    }, 400);

                } else {
                    setTimeout(function () {
                        $toggle.addClass('toggled');
                    }, 430);

                    div = '<div id="bodyClick"></div>';
                    $(div).appendTo("body").click(function () {
                        $('html').removeClass('nav-open');
                        lbd.misc.navbar_menu_visible = 0;
                        $('#bodyClick').remove();
                        setTimeout(function () {
                            $toggle.removeClass('toggled');
                        }, 400);
                    });

                    $('html').addClass('nav-open');
                    lbd.misc.navbar_menu_visible = 1;

                }
            });
            navbar_initialized = true;
        }

    }
};




function dialogClose() {

    window.location.reload();

    $.dialog().close();
};



var spinDialog = spinDialog || (function ($) {
        'use strict';

        // Creating modal dialog's DOM
        var $dialog = $(
            '<div class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="padding-top:15%; overflow-y:visible;">' +
            '<div class="modal-dialog modal-m">' +
            '<div class="modal-content">' +
            '<div class="modal-header"><h4 style="margin:0;"></h4></div>' +
            '<div class="modal-body">' +
            '<div class="progress progress-striped active" style="margin-bottom:0;"><div class="progress-bar" style="width: 100%"></div></div>' +
            '<p style="text-align:center;padding: 5px 0px 0px;font-size: 12px;color: rgb(140, 140, 140);margin-bottom: 0px;">Please wait..</p></div>' +
            '</div></div></div>');

        return {
            /**
             * Opens our dialog
             * @param message Custom message
             * @param options Custom options:
             *                  options.dialogSize - bootstrap postfix for dialog size, e.g. "sm", "m";
             *                  options.progressType - bootstrap postfix for progress bar type, e.g. "success", "warning".
             */
            show: function (message, options) {
                // Assigning defaults
                if (typeof options === 'undefined') {
                    options = {};
                }
                if (typeof message === 'undefined') {
                    message = 'Loading..';
                }
                var settings = $.extend({
                    dialogSize: 'sm',
                    progressType: '',
                    txtMessage: '',
                    onHide: null // This callback runs after the dialog is hidden
                }, options);

                // Configuring dialog
                $dialog.find('.modal-dialog').attr('class', 'modal-dialog').addClass('modal-' + settings.dialogSize);
                $dialog.find('.progress-bar').attr('class', 'progress-bar');
                if (settings.progressType) {
                    $dialog.find('.progress-bar').addClass('progress-bar-' + settings.progressType);
                }

                if (settings.txtMessage) {
                    $dialog.find('p').html(settings.txtMessage);
                }
                $dialog.find('h4').text(message);
                // Adding callbacks
                if (typeof settings.onHide === 'function') {
                    $dialog.off('hidden.bs.modal').on('hidden.bs.modal', function (e) {
                        settings.onHide.call($dialog);
                    });
                }
                // Opening dialog
                $dialog.modal();
            },
            /**
             * Closes dialog
             */
            hide: function () {
                $dialog.modal('hide');
            }
        };

    })(jQuery);
