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

    var group_list_container = document.getElementById('moveable_groups');
    if (group_list_container !== null) {
        var moveable_groups = new Sortable(group_list_container);
    }

    $('.settings_groups_list').each(function () {
        var el = document.getElementById($(this).attr('id'));
        var options = {
            group: {
                name: 'groups'
            },
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
            },
            onAdd: function (e) {
                var el = e.item;
                $(el).children('input').attr('name', 'users[' + $(e.to).attr('group-id') + '][]');
            }
        };
        if (count === 0) {
            options.group = {
                name: 'groups',
                put: false,
                pull: 'clone'
            };
        }

        var sortable = new Sortable(el, options);
        count++;
    });

    $('#settings_groups_add_new').on('click', function () {
        $('#settings_groups_add_form').show();
        $('#settings_groups_add_new_col').hide();
        $('#settings_groups_add_input').focus();
    });


    $('.fixform').on('click', function () {
        $('#settings_groups_savechanges_form').submit();
    });

});

var init_group_change_pic = function (id, lang) {
    $('#group_change_pic_id').val(id);
    $('#register_profile_pic').attr('src', '/' + lang + '/upload/grouppic/?id=' + id + '&size=256');
    $('#group_change_pic__delete').attr('href', '/' + lang + '/settings/groupspic.remove/?id=' + id);
};