/* global Sortable */

$(window).load(function () {

    var count = 0;

    var trash = document.getElementById('settings_groups_trash');
    var trashList = new Sortable(trash, {
        group: {
            name: 'groups',
            pull: false
        },
        onAdd: function (e) {
            $('div#settings_groups_trash').html('');
        }
    });

    $('.settings_groups_list').each(function () {
        var el = document.getElementById($(this).attr('id'));
        var options = {
            group: 'groups',
            animation: 150,
            scroll: true,
            scrollSensitivity: 100,
            scrollSpeed: 10,
            sort: false,
            onStart: function (e) {
                $('#settings_groups_trash').show();
            },
            onEnd: function (e) {
                $('#settings_groups_trash').hide();
            }
        };
        if (count === 0) {
            options.group = {
                name: 'groups',
                pull: 'clone',
                put: false
            };
        }

        var sortable = new Sortable(el, options);
        count++;
    });
});