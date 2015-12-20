$(window).load(function () {

    $('#dashboard_header_usermenu').on('click', function () {
        $('.overlay').show();
        $(this).parent().children('.dropdown').show();
    });

    $('.overlay').on('click', function () {
        $('.dropdown').hide();
        $('.overlay').hide();
    });
});