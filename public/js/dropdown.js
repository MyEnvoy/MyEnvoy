$(window).load(function () {

    $('#dashboard_header_usermenu').on('click', function () {
        $('.overlay').show();
        $(this).parent().children('.dropdown_usermenu').show();
    });

    $('#dashboard_header_notifications').on('click', function () {
        $('.overlay').show();
        $(this).parent().children('.dropdown_notify').show();
    });

    $('.overlay').on('click', function () {
        $('.dropdown').hide();
        $('.overlay').hide();
    });

    $('#notifications .row').on('click', function () {
        window.location.href = $(this).attr('href');
    });
});