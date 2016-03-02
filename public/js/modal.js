$(window).load(function () {

    $('.modal').each(function () {
        $(this).children('.modal_content').prepend('<span class="modal_close genericon genericon-close"></span>');
    });

    $('.modal_close').on('click', function () {
        $(this).parent('.modal_content').parent('.modal').fadeOut(50);
    });

    $('.show_modal').click(function () {
        var elId = $(this).attr('target-modal');
        $('#' + elId).fadeIn(50);
    });

});