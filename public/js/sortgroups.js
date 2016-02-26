/* global Sortable */

$(window).load(function () {

    var count = 0;

    $('.settings_groups_list').each(function () {
        var el = document.getElementById($(this).attr('id'));
        var sortable = new Sortable(el, {
            group: 'groups',
            animation: 150,
            scroll: true,
            scrollSensitivity: 30,
            scrollSpeed: 10
        });
    });

});