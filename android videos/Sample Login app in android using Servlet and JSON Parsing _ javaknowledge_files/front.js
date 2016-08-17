jQuery(function ($) {
    $('.wpdm-download-locked.pop-over').on('click',function () {

        var $dc = $($(this).attr('href'));
        if ($(this).attr('data-ready') == undefined) {

            $(this).popover({
                placement: 'top',
                html: true,
                content: function () {

                    return $dc.html();


                }
            });
            $(this).attr('data-ready', 'hide');
        }

        if ($(this).attr('data-ready') == 'hide'){
            $(this).popover('show');
            $(this).attr('data-ready', 'show');
        } else if ($(this).attr('data-ready') == 'show'){
            $(this).popover('hide');
            $(this).attr('data-ready', 'hide');
        }


    return false;
    });

});